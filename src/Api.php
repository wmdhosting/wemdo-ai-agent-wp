<?php
namespace Wemdo\AIAgent;

if (!defined('ABSPATH')) {
    exit;
}

class Api {
    const BACKEND_URL = 'https://ai.wmd.hr';

    /**
     * Verifies an API key against the AI Layer backend.
     *
     * @param string $key  e.g. "ak_..."
     * @return array {
     *   @type string $status  'valid' | 'invalid' | 'unreachable'
     *   @type array  $data    On 'valid': tenant_id/name/plan/widget_install_url
     *   @type string $message Human-readable for inline UI
     * }
     */
    public static function verify_key(string $key): array {
        if (empty($key)) {
            return ['status' => 'invalid', 'data' => [], 'message' => __('API key is empty.', 'wemdo-ai-agent')];
        }
        $url = self::BACKEND_URL . '/api/wp/verify-key';
        $response = wp_remote_get($url, [
            'timeout'   => 10,
            'sslverify' => true,
            'headers'   => ['x-api-key' => $key],
        ]);
        if (is_wp_error($response)) {
            return [
                'status'  => 'unreachable',
                'data'    => [],
                'message' => sprintf(
                    /* translators: %s = error message */
                    __('Couldn\'t reach AI Layer: %s', 'wemdo-ai-agent'),
                    $response->get_error_message()
                ),
            ];
        }
        $code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);
        if ($code === 401) {
            return ['status' => 'invalid', 'data' => [], 'message' => __('Key invalid or expired. Get a fresh one at AI Layer admin.', 'wemdo-ai-agent')];
        }
        if ($code !== 200 || !is_array($body) || empty($body['valid'])) {
            return ['status' => 'unreachable', 'data' => [], 'message' => __('Unexpected response from AI Layer. Settings will be saved anyway.', 'wemdo-ai-agent')];
        }
        return [
            'status'  => 'valid',
            'data'    => [
                'tenant_id'         => $body['tenant_id'] ?? '',
                'tenant_name'       => $body['tenant_name'] ?? '',
                'plan'              => $body['plan'] ?? '',
                'widget_install_url' => $body['widget_install_url'] ?? '',
            ],
            'message' => sprintf(
                /* translators: 1: tenant name, 2: plan name */
                __('Connected to tenant %1$s (%2$s plan)', 'wemdo-ai-agent'),
                $body['tenant_name'] ?? '?',
                $body['plan'] ?? '?'
            ),
        ];
    }
}
