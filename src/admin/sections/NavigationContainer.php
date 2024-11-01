<?php

namespace SpatialMatchIdx\admin\sections;


class NavigationContainer
{
    public function getAllActiveRegisteredMenu(): array
    {
        return wp_get_nav_menus();
    }

    /**
     * @return false|\WP_Term
     */
    public static function getPrimaryMenu()
    {
        $locations = get_nav_menu_locations();

        if (!empty($locations['primary'])) {
            // Get object id by location
            return wp_get_nav_menu_object($locations['primary']);
        }

        if (is_array($locations) && count($locations) > 1 ) {
            $menuArrayKeys = array_keys($locations);

            if (!empty($locations[$menuArrayKeys[1]])) {
                return wp_get_nav_menu_object($locations[$menuArrayKeys[1]]);
            }
        }

        $menus = wp_get_nav_menus();

        return (is_array($menus) && count($menus) > 1) ? $menus[0] : null;
    }

    /**
     * @param $menuSlug
     * @return array
     */
    public static function getMenuItems($menuSlug):array
    {
        $items = [];

        if (empty($menuSlug)) {
            return $items;
        }

        if (!empty($menuSlug)) {
            $wpMenuObject = wp_get_nav_menu_items($menuSlug);
        } else {
            $locations = get_nav_menu_locations();

            if (count($locations) === 0) {
                $items = [];
            }

            if (isset($locations['primary'])) {
                // Get object id by location
                $menuId = wp_get_nav_menu_object($locations['primary']);
            } elseif (is_array($locations) && count($locations) > 0) {
                $menuArrayKeys = array_keys($locations);
                $menuId = wp_get_nav_menu_object($locations[$menuArrayKeys[0]]);
            }

            $wpMenuObject = !empty($menuId) ? wp_get_nav_menu_items($menuId) : false;
        }

        if ($wpMenuObject !== false) {
            /**@var $menuItem \WP_Post*/
            $i = 0;
            $j = 0;
            while ($i < count($wpMenuObject)) {
                if (isset($wpMenuObject[$i]->menu_item_parent) && ((int)$wpMenuObject[$i]->menu_item_parent) > 0) {
                    /* TODO:  recursion call for create submenu */
                    $parentMenuItemIdArray = [];
                    $parentMenuItemIdArray[] = $wpMenuObject[$i]->menu_item_parent;
                    $result = self::buildChildrenMenuItems(
                        $wpMenuObject,
                        $parentMenuItemIdArray,
                        $wpMenuObject[$i]->menu_item_parent,
                        $i
                    );
                    $item['children'] = $result['children'];
                    $items[$j-1] = $item;
                    $i = $result['counter'];
                    $item = [];
                } else {
                    $post = get_post($wpMenuObject[$i]->object_id);
                    $item['caption'] = !empty($wpMenuObject[$i]->title) ? $wpMenuObject[$i]->title : $post->post_title;
                    $item['href'] = !empty($wpMenuObject[$i]->url) ? $wpMenuObject[$i]->url : get_the_permalink($post);
                    $items[] = $item;
                    $i++;
                    $j++;
                }
            }
        }

        return $items;
    }

    /**
     * @param $wpMenuObject array
     * @param $parentMenuItemIdArray
     * @param $parentItemId string
     * @param $counter
     *
     * @return array
     */
    private static function buildChildrenMenuItems($wpMenuObject, $parentMenuItemIdArray, $parentItemId, $counter)
    {
        $items = [];
        $i = $counter;
        while ($i < count($wpMenuObject)) {
            if (
                $wpMenuObject[$i]->menu_item_parent !== $parentItemId
                && $wpMenuObject[$i]->menu_item_parent !== '0'
                && $wpMenuObject[$i]->menu_item_parent !== $wpMenuObject[$counter-1]->menu_item_parent
                && !in_array($wpMenuObject[$i]->menu_item_parent, $parentMenuItemIdArray, false)
            ) {
                $parentMenuItemIdArray[] = $wpMenuObject[$i]->menu_item_parent;
                $result = self::buildChildrenMenuItems(
                    $wpMenuObject,
                    $parentMenuItemIdArray,
                    $wpMenuObject[$i]->menu_item_parent,
                    $i
                );
                $item['children'] = $result['children'];
                $i = $result['counter'];
                $count = count($items) - 1;
                $items[$count] = $item;
                $item = [];
            } elseif ($wpMenuObject[$i]->menu_item_parent === $parentItemId && $wpMenuObject[$i]->menu_item_parent !== '0') {
                $post = get_post($wpMenuObject[$i]->object_id);
                $item['caption'] = !empty($wpMenuObject[$i]->title) ? $wpMenuObject[$i]->title : $post->post_title;
                $item['href'] = !empty($wpMenuObject[$i]->url) ? $wpMenuObject[$i]->url : get_the_permalink($post);
                $items[] = $item;
                $i++;
            } else {

                return ['children' => $items, 'counter' => $i];
            }
        }

        return ['children' => $items, 'counter' => $i];
    }
}
