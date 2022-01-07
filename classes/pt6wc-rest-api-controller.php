<?php

/**
 * API class
 */
class Printy6_REST_API_Controller extends WC_REST_Controller
{
    /**
     * Endpoint namespace.
     *
     * @var string
     */
    protected $namespace = 'printy6';

    /**
     * Route base.
     *
     * @var string
     */
    protected $rest_base = 'wc/v1';

    /**
     * Register the REST API routes.
     */
    public function register_routes() {
        $this->register_access();
        $this->register_access_check();

        // Rest API
        // $this->register_init();
        // $this->register_get_order();
        // $this->register_get_orders();
        // $this->register_get_product();
        // $this->register_get_products();
        // $this->register_get_templates();
        // $this->register_get_stores();
        // $this->register_get_invoice();
        // $this->register_get_invoices();

        // 测试用 记得删除
        // $this->register_get_settings();
    }

    public function requestPrinty6OpenApi($slug, $method = 'GET', $data = [], $settings = [])
    {
        if(!$settings) {
            $settings = PT6_Base::get_settings();
        }

        if(!isset($settings['user_id']) || !isset($settings['access_token'])) {
			return new WP_Error( 'printy6_open_api unauthorized', "Sorry, you cannot list resources.", array( 'status' => 401 ) );
        }

        $response = wp_remote_request( PT6WC_API_DOMAIN . $slug, [
            'method' => $method,
            'headers' => [
                "Auth-User-Id" => $settings['user_id'],
                "Auth-Token" => "Bearer " . $settings['access_token'],
            ],
            'body' => $data,
        ] );

        $responseCode = wp_remote_retrieve_response_code($response);

        if(substr($responseCode, 0 , 1) === "2") {
            $body = wp_remote_retrieve_body( $response );

            return json_decode($body, true);
        }

        switch ($variresponseCodeable) {
            case '401':
                return new WP_Error( 'printy6_open_api unauthorized', "Sorry, you cannot list resources.", array( 'status' => 401 ) );
            
            case '404':
                return new WP_Error( 'not_found', "Sorry, you cannot found this resources.", array( 'status' => 404 ) );
            
            case '422':
                $message = wp_remote_retrieve_response_message($response);
                return new WP_Error( 'unprocessable_entity', $message ? $message : "Sorry, data is wrong.", array( 'status' => 422 ) );

            default:
                return $response;
        }
    }

    public function register_access() {
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/access', array(
            array(
                'methods' => "POST",
                'callback' => array( $this, 'set_printy6_access' ),
                'permission_callback' => array( $this, 'get_public_permissions' ),
                // 'permission_callback' => array( $this, 'get_items_permissions_check' ),
                'show_in_index' => false,
                'args' => array(
                    'access_token' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => __( 'Printy6 access key', 'printy6' ),
                    ),
                    'user_id' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => __( 'User Identifier', 'printy6' ),
                    ),
                    'store_id' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => __( 'Store Identifier', 'printy6' ),
                    ),
                ),
            )
        ) );
    }

    /**
     * @param WP_REST_Request $request
     * @return array
     */
    public function set_printy6_access( $request )
    {
        $user_id = $request->get_param('user_id');
        $store_id = $request->get_param('store_id');
        $access_token  = $request->get_param('access_token');

        $response = $this->requestPrinty6OpenApi('/stores/' . $store_id, 'GET', [], [
            'user_id' => $user_id,
            'store_id' => $store_id,
            'access_token' => $access_token,
        ]);
        
        if(!is_array($response)) {
            return [
                'success' => false,
            ];
        }

        if(!isset($response['id'])) {
            return [
                'success' => false,
            ];
        }

        PT6_Base::update_settings([
            'user_id' => $user_id,
            'store_id' => $store_id,
            'access_token' => $access_token,
        ]);

        return [
            'success' => true,
        ];
    }

    public function register_access_check()
    {
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/access/check', array(
            array(
                'methods' => "GET",
                'callback' => array( $this, 'check_printy6_access' ),
                'permission_callback' => array( $this, 'get_public_permissions' ),
                'show_in_index' => false,
            )
        ) );
    }
    
    public function check_printy6_access()
    {
        $settings = PT6_Base::get_settings();

        $response = $this->requestPrinty6OpenApi('/stores/' . $settings['store_id'], 'GET');

        if(!is_array($response)) {
            return $response;
        }

        if(!isset($response['id'])) {
            return [
                'success' => false,
            ];
        }

        return [
            'success' => true,
        ];
    }

    public function register_get_settings()
    {
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/settings', array(
            array(
                'methods' => "GET",
                'permission_callback' => array( $this, 'get_public_permissions' ),
                'callback' => array( $this, 'get_settings' ),
                'show_in_index' => false,
            )
        ) );
    }

    public function get_settings($request) {
        // PT6_Base::update_settings([], true);
        // PT6_Base::update_settings([
        //     "user_id" => "3f7a52e9-a94a-46be-8c46-59e408377c8d",
        //     "store_id" => "b08b07a5-9353-437a-a324-4b952958a307",
        //     "access_token" => "mloxBPRgvATlfY3bNaqvSCLUJGpIW6u4D799ncoZ"
        // ]);
        $settings = PT6_Base::get_settings();

        return $settings;
    }

    public function register_init()
    {
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/init', array(
            array(
                'methods' => "GET",
                'permission_callback' => array( $this, 'get_public_permissions' ),
                'callback' => array( $this, 'get_init' ),
                'show_in_index' => false,
            )
        ) );
    }

    public function get_init($request) {
        return $this->requestPrinty6OpenApi('/init');
    }

    public function register_get_order()
    {
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/orders/(?P<order_id>\d+)', array(
            array(
                'methods' => "GET",
                'permission_callback' => array( $this, 'get_public_permissions' ),
                'callback' => array( $this, 'get_order' ),
                'show_in_index' => false,
                'args' => array(
                    'order_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Store Identifier',
                    ),
                ),
            )
        ) );
    }

    public function get_order($request) {
        $orderId = $request->get_param('order_id');
        return $this->requestPrinty6OpenApi('/orders/' . $orderId);
    }

    public function register_get_orders()
    {
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/orders', array(
            array(
                'methods' => "GET",
                'permission_callback' => array( $this, 'get_public_permissions' ),
                'callback' => array( $this, 'get_orders' ),
                'show_in_index' => false,
                'args' => array(
                    'status' => array(
                        'type' => 'string',
                        'description' => 'Orders status',
                    ),
                    'page' => array(
                        'type' => 'integer',
                        'description' => 'Orders list page',
                    ),
                    'limit' => array(
                        'type' => 'integer',
                        'description' => 'Orders per page items quantity',
                    ),
                    'start_time' => array(
                        'type' => 'string',
                        'description' => 'Orders create from time',
                    ),
                    'end_time' => array(
                        'type' => 'string',
                        'description' => 'Orders create to time',
                    ),
                ),
            )
        ) );
    }

    public function get_orders($request) {
        $params = $request->get_params();
        return $this->requestPrinty6OpenApi('/orders?' . http_build_query($params));
    }

    public function register_get_product()
    {
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/products/(?P<product_id>\d+)', array(
            array(
                'methods' => "GET",
                'permission_callback' => array( $this, 'get_public_permissions' ),
                'callback' => array( $this, 'get_product' ),
                'show_in_index' => false,
                'args' => array(
                    'product_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Store Identifier',
                    ),
                ),
            )
        ) );
    }

    public function get_product($request) {
        $productId = $request->get_param('product_id');
        return $this->requestPrinty6OpenApi('/products/' . $productId);
    }

    public function register_get_products()
    {
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/products', array(
            array(
                'methods' => "GET",
                'permission_callback' => array( $this, 'get_public_permissions' ),
                'callback' => array( $this, 'get_products' ),
                'show_in_index' => false,
                'args' => array(
                    'template_id' => array(
                        'type' => 'boolean',
                        'description' => 'Template Identifier',
                    ),
                    'page' => array(
                        'type' => 'integer',
                        'description' => 'Products list page',
                    ),
                    'limit' => array(
                        'type' => 'integer',
                        'description' => 'Products per page items quantity',
                    ),
                ),
            )
        ) );
    }

    public function get_products($request) {
        $params = $request->get_params();
        return $this->requestPrinty6OpenApi('/products?' . http_build_query($params));
    }
    
    public function register_get_templates()
    {
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/templates', array(
            array(
                'methods' => "GET",
                'permission_callback' => array( $this, 'get_public_permissions' ),
                'callback' => array( $this, 'get_templates' ),
                'show_in_index' => false,
                'args' => array(
                    'is_recommend' => array(
                        'type' => 'boolean',
                        'description' => 'Template is recommended',
                    ),
                    'page' => array(
                        'type' => 'integer',
                        'description' => 'Templates list page',
                    ),
                    'limit' => array(
                        'type' => 'integer',
                        'description' => 'Templates per page items quantity',
                    ),
                ),
            )
        ) );
    }

    public function get_templates($request) {
        $params = $request->get_params();
        return $this->requestPrinty6OpenApi('/templates?' . http_build_query($params));
    }

    public function register_get_template()
    {
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/templates/(?P<template_id>\d+)', array(
            array(
                'methods' => "GET",
                'permission_callback' => array( $this, 'get_public_permissions' ),
                'callback' => array( $this, 'get_template' ),
                'show_in_index' => false,
                'args' => array(
                    'template_id' => array(
                        'required' => true,
                        'type' => 'int',
                        'description' => 'Template Identifier',
                    ),
                ),
            )
        ) );
    }

    public function get_template($request) {
        $templateId = $request->get_param('template_id');
        return $this->requestPrinty6OpenApi('/templates/' . $templateId);
    }
    
    public function register_get_stores()
    {
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/stores', array(
            array(
                'methods' => "GET",
                'permission_callback' => array( $this, 'get_public_permissions' ),
                'callback' => array( $this, 'get_stores' ),
                'show_in_index' => false,
            )
        ) );
    }

    public function get_stores($request) {
        return $this->requestPrinty6OpenApi('/stores');
    }

    public function register_get_invoice()
    {
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/invoices/(?P<invoice_id>\d+)', array(
            array(
                'methods' => "GET",
                'permission_callback' => array( $this, 'get_public_permissions' ),
                'callback' => array( $this, 'get_invoice' ),
                'show_in_index' => false,
                'args' => array(
                    'invoice_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Store Identifier',
                    ),
                ),
            )
        ) );
    }

    public function get_invoice($request) {
        $invoiceId = $request->get_param('invoice_id');
        return $this->requestPrinty6OpenApi('/invoices/' . $invoiceId);
    }

    public function register_get_invoices()
    {
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/invoices', array(
            array(
                'methods' => "GET",
                'permission_callback' => array( $this, 'get_public_permissions' ),
                'callback' => array( $this, 'get_invoices' ),
                'show_in_index' => false,
                'args' => array(
                    'page' => array(
                        'type' => 'integer',
                        'description' => 'Invoices list page',
                    ),
                    'limit' => array(
                        'type' => 'integer',
                        'description' => 'Invoices per page items quantity',
                    ),
                    'start_time' => array(
                        'type' => 'string',
                        'description' => 'Invoices create from time',
                    ),
                    'end_time' => array(
                        'type' => 'string',
                        'description' => 'Invoices create to time',
                    ),
                ),
            )
        ) );
    }

    public function get_invoices($request) {
        $params = $request->get_params();
        return $this->requestPrinty6OpenApi('/invoices?' . http_build_query($params));
    }


    /**
     * Check whether a given request has permission to read printy6 endpoints.
     *
     * @return boolean
     */
    public function get_public_permissions( $request ) {
        return true;
    }

    /**
     * Check whether a given request has permission to read printy6 endpoints.
     *
     * @param  WP_REST_Request $request Full details about the request.
     * @return WP_Error|boolean
     */
    public function get_items_permissions_check( $request ) {
        if ( ! wc_rest_check_user_permissions( 'read' ) ) {
            return new WP_Error( 'woocommerce_rest_cannot_view', __( 'Sorry, you cannot list resources.', 'woocommerce' ), array( 'status' => rest_authorization_required_code() ) );
        }

        return true;
    }
}
