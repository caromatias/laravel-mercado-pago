<?php

namespace LaravelMercadoPago\Entity;

use MercadoPago\Client\PaymentMethod\PaymentMethodClient;
use MercadoPago\MercadoPagoConfig;
use LaravelMercadoPago\Traits\EntityTrait;
use LaravelMercadoPago\Interfaces\ClassToJson;

/**
 * PaymentMethod class
 */
class PaymentMethod implements ClassToJson
{
    use EntityTrait;

    /**
     * Propiedades para almacenar los datos del método de pago
     */
    protected $id;
    protected $name;
    protected $payment_type_id;
    protected $status;
    protected $secure_thumbnail;
    protected $thumbnail;
    protected $deferred_capture;
    protected $settings;
    protected $additional_info_needed;
    protected $min_allowed_amount;
    protected $max_allowed_amount;
    protected $accreditation_time;
    protected $financial_institutions;
    protected $processing_modes;
    
    /**
     * Cliente de MercadoPago para métodos de pago
     */
    private $client;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->client = new PaymentMethodClient();
    }
    
    /**
     * Consultar los medios de pago disponibles (con filtros)
     * @link https://www.mercadopago.com.co/developers/es/reference/payment_methods/_payment_methods/get
     * @return array
     */
    public function findV2($filters = [])
    {
        try {
            $response = $this->client->list();
            
            if (!empty($filters) && isset($filters['bins'])) {
                $cardBin = $filters['bins'];
                $filteredMethods = array_filter($response, function($method) use ($cardBin) {
  
                    return isset($method->settings) && 
                           isset($method->settings->bin) && 
                           strpos($method->settings->bin, substr($cardBin, 0, 6)) === 0;
                });
                return array_values($filteredMethods);
            }
            
            return $response;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Consultar los medios de pago disponibles 
     * @link https://www.mercadopago.com.co/developers/es/reference/payment_methods/_payment_methods/get
     * @return array
     */
    public function find()
    {
        try {
            return $this->client->list();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Obtener tipo de targeta de credito por el numero
     * @param string $cardNumber
     * @return PaymentMethod
     */
    public function findCreditCard($cardNumber)
    {
        $credit = $this->findV2(['bins' => $cardNumber]);
        return !empty($credit) ? $credit[0] : null;
    }
    
    /**
     * Método estático para compatibilidad con código existente
     * @return array
     */
    public static function all()
    {
        $instance = new self();
        return $instance->find();
    }
    
    /**
     * Método estático para compatibilidad con código existente
     * @param array $filters
     * @return array
     */
    public static function search($filters = [])
    {
        $instance = new self();
        return $instance->findV2($filters);
    }
    
    /**
     * Setter mágico para mantener compatibilidad
     */
    public function __set($name, $value)
    {
        $this->$name = $value;
    }
    
    /**
     * Getter mágico para mantener compatibilidad
     */
    public function __get($name)
    {
        return $this->$name ?? null;
    }
}
