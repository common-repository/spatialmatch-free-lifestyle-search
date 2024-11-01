<?php

namespace SpatialMatchIdx\services;

class AdminNoticesService
{
    const MESSAGE_INFO = 'info';
    const MESSAGE_WARNING = 'warning';
    const MESSAGE_ERROR = 'error';
    const MESSAGE_SUCCESS = 'success';

    /**
     * @var array
     */
    private static $instances = [];

    /**
     * @var array
     */
    private $messages = [];

    public function __construct()
    {
        $this->messages = $_SESSION['flashMessages'] ?? [];
    }

    /**
     * @return AdminNoticesService
     */
    public static function getInstance(): AdminNoticesService
    {
        $cls = static::class;
        if (!isset(self::$instances[$cls])) {
            self::$instances[$cls] = new static();
        }

        return self::$instances[$cls];
    }

    /**
     * @param string $message
     * @param string $type
     */
    public function addMessage(string $message, string $type = AdminNoticesService::MESSAGE_INFO)
    {
        $this->messages[] = [
            'type' => $type,
            'message' => $message,
        ];

        $_SESSION['flashMessages'] = $this->messages;
    }

    public function clearMessages()
    {
        $this->messages = [];

        $_SESSION['flashMessages'] = $this->messages;
    }

    public function showMessages()
    {
        $messages = '';
        
        foreach ($this->messages as $message) {
            $messages .= sprintf('<div class="notice notice-%s is-dismissible">
                <p>%s</p>
            </div>', $message['type'], $message['message']);
        }
        
        if ('' !== $messages) {
            add_action('admin_notices', function () use ($messages){
                echo $messages;
            });
        }

        $this->clearMessages();
    }
}
