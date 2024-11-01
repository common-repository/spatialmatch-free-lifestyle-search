<?php

namespace SpatialMatchIdx\admin\actions;

use SpatialMatchIdx\core\actions\interfaces\ActionInterface;

abstract class BaseAjaxAction implements ActionInterface
{
    /**
     * @param $data
     */
    protected function success($data)
    {
        $this->response(array_merge([
            'success' => true,
        ], $data));
    }

    /**
     * @param string $message
     * @param null $data
     */
    protected function error(string $message, $data = null)
    {
        $this->response([
            'success' => false,
            'message' => $message,
        ]);
    }

    /**
     * @param $data
     */
    private function response($data)
    {
        die(json_encode($data));
    }
}
