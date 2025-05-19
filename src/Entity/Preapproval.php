<?php
namespace LaravelMercadoPago\Entity;

use LaravelMercadoPago\Traits\EntityTrait;
use LaravelMercadoPago\Interfaces\ClassToJson;
use MercadoPago\Client\PreApproval\PreApprovalClient;
use MercadoPago\Exceptions\MPApiException;

class Preapproval implements ClassToJson
{
    use EntityTrait;

    // Propiedades de la suscripción
    public $id;
    public $back_url;
    public $collector_id;
    public $application_id;
    public $external_reference;
    public $reason;
    public $auto_recurring;
    public $card_id;
    public $card_token_id;
    public $payer_email;
    public $payer_id;
    public $status;
    public $preapproval_plan_id;
    public $init_point;
    public $sandbox_init_point;
    public $payment_method_id;

    // Cliente de API para suscripciones
    private $client;

    public function __construct()
    {
        $this->client = new PreApprovalClient();
        // Inicializar auto_recurring con valores predeterminados
        $this->auto_recurring = [
            'frequency' => 1,
            'frequency_type' => 'months',
            'transaction_amount' => 0,
            'currency_id' => 'COP'
        ];
    }

    /**
     * Guarda o actualiza la suscripción
     * @return bool
     */
    public function save()
    {
        try {
            // Convertir la instancia a un array para la API
            $data = $this->toArray();
            
            if (isset($this->id)) {
                // Actualizar una suscripción existente
                $response = $this->client->update($this->id, $data);
            } else {
                // Crear una nueva suscripción
                $response = $this->client->create($data);
            }
            
            // Actualizar propiedades con la respuesta
            foreach ($response as $key => $value) {
                $this->$key = $value;
            }
            
            return true;
        } catch (MPApiException $e) {
            // Loguear el error para debugging
            error_log('MercadoPago API Error: ' . $e->getMessage());
            return false;
        } catch (\Exception $e) {
            // Loguear otros errores
            error_log('Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca una suscripción por ID
     * @param string $id ID de la suscripción
     * @return Preapproval|null
     */
    public function findById($id)
    {
        try {
            $response = $this->client->get($id);
            
            // Actualizar propiedades con la respuesta
            foreach ($response as $key => $value) {
                $this->$key = $value;
            }
            
            return $this;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Listar todas las suscripciones
     * @param array $filters Filtros de búsqueda
     * @return array
     */
    public function find($filters = [])
    {
        try {
            $response = $this->client->search(['filters' => $filters]);
            return $response->getResponse()['results'] ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Convierte las propiedades a un array para la API
     * @return array
     */
    public function toArray()
    {
        $result = [];
        // Obtener todas las propiedades públicas
        $properties = get_object_vars($this);
        
        // Excluir propiedades internas
        unset($properties['client']);
        
        // Filtrar propiedades no nulas
        foreach ($properties as $key => $value) {
            if ($value !== null) {
                $result[$key] = $value;
            }
        }
        
        return $result;
    }
    
    /**
     * Método para compatibilidad con el código anterior
     */
    public function update()
    {
        return $this->save();
    }
    
    /**
     * Setter mágico para compatibilidad
     */
    public function __set($name, $value)
    {
        $this->$name = $value;
    }
    
    /**
     * Getter mágico para compatibilidad
     */
    public function __get($name)
    {
        return $this->$name ?? null;
    }
}
