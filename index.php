<?php

namespace NamePlugin;

class NameApi {
    public $api_url;

    public function list_vacansies($post, $vid = 0) {
        global $wpdb;

        if (!is_object($post)) {
            return false;
        }
        //Я переместил переменную $ret сюда, потому что она нам не нужна, если $post не является объектом.
        $ret = array();
        $page = 0;
        $found = false;
        
        l1:
        $params = $this->buildQueryParams('superjob_user_id', $page);
        $res = $this->api_send($this->api_url . '/hr/vacancies/?' . $params);

        // Эту часть я удалил, так как уже занимаюсь декодированием в api_send запросе
        //$res_o = json_decode($res);

        if (is_object($res) && isset($res->objects)) {
            $ret = array_merge($res->objects, $ret);
            if ($vid > 0) // Для конкретной вакансии, иначе возвращаем все
                foreach ($res->objects as $key => $value) {
                    if ($value->id == $vid) {
                        $found = $value;
                        break;
                    }
                }

            if ($found === false && $res->more) {
                $page++;
                goto l1;
            } else {
                if (is_object($found)) {
                    return $found;
                } else {
                    return $ret;
                }
            }
        } 
        // Эта часть нам не нужна, потому что это лишний код.
        // else {
        //     return false;
        // }

        return false;
    }    
    
    public function api_send(string $requestUrl) 
    {
        //Я написал код для GET запроса и обработки ошибок.
        try {
            $response = wp_remote_get( $requestUrl );

            if ( is_array( $response ) && ! is_wp_error( $response ) ) {
                $body = wp_remote_retrieve_body( $response );
                $data = json_decode( $body );
                return $data;
            } else {
                throw new \Exception("Ошибка, пожалуйста, проверьте ответ на запрос от backend");
            }
        } catch (Exception $e) {
            throw new \Exception("Ошибка во время GET запроса:" . $$e->getMessage() );
        }
    }

    public function self_get_option($option_name) 
    {
        $option_value = get_option($option_name);

        return ($option_value !== false) ? $option_value : "";
    }

    
    // Я добавил эту функциональность построителя параметров запроса в отдельную функцию, 
    // чтобы сделать код понятным.
    public function buildQueryParams( string $self_get_option_param, int $page )
    {
        $responseParams = [
            'status' => 'all',
            'id_user' => this->self_get_option($self_get_option_param),
            'with_new_response' => 0,
            'order_field' => 'date',
            'order_direction' => 'desc',
            'page' => $page,
            'count' => 100
        ];
        
        return http_build_query($responseParams);
    }
}