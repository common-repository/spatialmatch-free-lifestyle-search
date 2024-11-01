<?php

namespace SpatialMatchIdx\core\routes;

use SpatialMatchIdx\core\actions\interfaces\ActionInterface;

class Router
{
    const DEFAULT_ACTION = 'license';

    /**
     * @var array
     */
    private $actionsMap = [];

    /**
     * @var array
     */
    private $actionsAjaxMap = [];

    /**
     * @param string $page
     * @param $slug
     * @param string $action
     * @param string $stage
     */
    public function addRoute(string $page, $slug, string $action, string $stage)
    {
        if ($action instanceof ActionInterface) {
            throw new \InvalidArgumentException('Argument action should be class implementing ActionInterface');
        }

        $this->actionsMap[$page][$slug ?: self::DEFAULT_ACTION][$stage] = $action;
    }

    /**
     * @param string $ajaxAction
     * @param string $actionClass
     */
    public function addAjaxRoute(string $ajaxAction, string $actionClass)
    {
        if ($actionClass instanceof ActionInterface) {
            throw new \InvalidArgumentException('Argument $actionClass should be class implementing ActionInterface');
        }

        $this->actionsAjaxMap[$ajaxAction] = $actionClass;
    }

    /**
     * @param array $request
     * @param string $stage
     * @return mixed|null
     */
    public function route(array $request, string $stage)
    {
        if (!isset($request['page'], $this->actionsMap[$request['page']])) {
            return null;
        }

        $pageActions = $this->actionsMap[$request['page']];
        $action = $request['action'] ?? self::DEFAULT_ACTION;

        if (!isset($pageActions[$action])) {
            return null;
        }

        return $pageActions[$action][$stage] ?? null;
    }

    /**
     * @param string $ajaxAction
     * @return mixed|null
     */
    public function ajaxRoute(string $ajaxAction)
    {
        return $this->actionsAjaxMap[$ajaxAction] ?? null;
    }
}
