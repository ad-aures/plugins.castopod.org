<?php

declare(strict_types=1);

if (! function_exists('htmx_alert')) {
    /**
     * @param array<string,string>|string $message
     */
    function htmx_alert(string $type, array|string $message): string
    {
        if (is_string($message)) {
            $message = [$message];
        }

        $alerts = [];
        foreach ($message as $m) {
            $alerts[] = [$type, $m];
        }

        return sprintf('<div hx-swap-oob="innerHTML:#alerts">%s</div>', view('_alerts', [
            'alerts' => $alerts,
        ]));
    }
}
