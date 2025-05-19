<?php

namespace LaravelMercadoPago;

use MercadoPago\MercadoPagoConfig;
use MercadoPago\Net\MPRestClient;
use MercadoPago\Client\MercadoPagoClient;
use LaravelMercadoPago\Traits\EntityTrait;

use LaravelMercadoPago\Entity\{
  Preapproval,
  Plan,
  PaymentMethod,
  OAuth,
  Card,
  IdentificationType,
  MerchantOrder,
  InstoreOrderV2,
  Store,
  InstoreOrderQr,
  AuthorizedPayment,
  Payment,
  Customer,
  Preference,
  POS,
  Refund,
  Payer,
  Item,
  Chargeback,
  CardToken,
  Issuer
};

class MercadoPago
{
  use EntityTrait;

  public function __construct()
  {
    $this->initSdk();
  }

  /**
   * función para comprobar el uso del facade a esta clase
   */
  public function hello()
  {
    return 'hello';
  }

  /**
   * Instancia de Payment
   * @return Payment
   */
  public function payment()
  {
    return new Payment();
  }

  /**
   * Instancia de Store
   * @return Store
   */
  public function store()
  {
    return new Store();
  }

  /**
   * Instancia de  AuthorizedPayment
   * @return  AuthorizedPayment
   */
  public function authorizedPayment()
  {
    return new AuthorizedPayment();
  }

  /**
   * Instancia de InstoreOrder
   * @return InstoreOrderV2
   * @link https://www.mercadopago.com/developers/es/reference/qr-code/_instore_orders_qr_user_id/post
   */
  public function instoreOrder()
  {
    // En SDK v3 ya no existe InstoreOrder, usamos InstoreOrderV2
    return new InstoreOrderV2();
  }

  /**
   * Instancia de InstoreOrderQr
   * @return InstoreOrderQr
   * @link https://www.mercadopago.com/developers/es/reference/qr-dynamic/_instore_orders_qr_seller_collectors_user_id_pos_external_pos_id_qrs/post
   */
  public function instoreOrderQr()
  {
    return new InstoreOrderQr();
  }

  /**
   * Instancia de CardToken
   * @return CardToken
   */
  public function cardToken()
  {
    return new CardToken();
  }

  /**
   * Instancia de InstoreOrderV2
   * @return InstoreOrderV2
   */
  public function instoreOrderV2()
  {
    return new InstoreOrderV2();
  }

  /**
   * Instancia de Issuer
   * @return Issuer
   */
  public function issuer()
  {
    return new Issuer();
  }

  /**
   * Instancia de MerchantOrder
   * @return MerchantOrder
   */
  public function merchantOrder()
  {
    return new MerchantOrder();
  }

  /**
   * Instancia de PaymentMethod
   * @return PaymentMethod
   */
  public function paymentMethod()
  {
    return new PaymentMethod();
  }

  /**
   * Instancia de POS
   * @return POS
   */
  public function pos()
  {
    return new POS();
  }

  /**
   * Instancia de IdentificationType
   * @return IdentificationType
   */
  public function identificationType()
  {
    return new IdentificationType();
  }

  /**
   * Instancia de Payer
   * @return Payer
   */
  public function payer()
  {
    return new Payer();
  }

  /**
   * Instancia de Refund
   * @return Refund
   */
  public function refund()
  {
    return new Refund();
  }

  /**
   * Instancia de Customer
   * @return Customer
   */
  public function customer()
  {
    return new Customer();
  }

  /**
   * Instancia de Card
   * @return Card
   */
  public function card()
  {
    return new Card();
  }

  /**
   * Instancia de Plan
   * @return Plan
   */
  public function plan()
  {
    return new Plan();
  }

  /**
   * Instancia de OAuth
   * @return OAuth
   */
  public function oauth()
  {
    return new OAuth();
  }

  /**
   * Instancia de Preapproval
   * @return Preapproval
   */
  public function preapproval()
  {
    return new Preapproval();
  }

  /**
   * Instancia de Preference
   * @return Preference
   */
  public function preference()
  {
    return new Preference();
  }

  /**
   * Instancia de Item
   * @return Item
   */
  public function item()
  {
    return new Item();
  }

  /**
   * Instancia de Chargeback 
   * @return Chargeback
   */
  public function chargeback()
  {
    return new Chargeback();
  }

  /**
   * País de localidad de cuenta de mercado pago
   * @return string
   */
  public function getCountryId()
  {
    // En SDK v3 no existe getCountryId, podríamos implementar un método personalizado
    // basado en el token o usar una configuración
    return config('mercado-pago.country_id', 'MCO');
  }

  /**
   *  Crear suscripción
   * @param string|null $back_url url de redirección despues del pago
   * @param string $reason descripción de la suscripción
   * @return Preapproval
   */
  public function createPreapproval($reason, $back_url = null)
  {
    $preapproval = $this->preapproval();
    $preapproval->back_url = $back_url ?? $this->getCallbackUrl();
    $preapproval->reason = $reason;

    return $preapproval;
  }

  /**
   *  Crear plan
   * @param string $back_url url de redirección una vez terminado el pago
   * @param string $description descripcion del plan (reason)
   * @return Plan
   * @link https://www.mercadopago.com.co/developers/es/reference/subscriptions/_preapproval_plan/post
   */
  public function createPlan($description, $back_url = null)
  {
    $plan = $this->plan();
    $plan->reason = $description;
    $plan->back_url = $back_url ?? $this->getCallbackUrl();

    return $plan;
  }

  /**
   * Inicializa el sdk de mercado pago
   * @link https://github.com/mercadopago/sdk-php
   */
  public function initSdk($access_token = null)
  {
    MercadoPagoConfig::setAccessToken($access_token ?? $this->getAccesToken());
  }

  /**
   * crear usuarios para hacer test
   * @link https://www.mercadopago.com.co/developers/es/reference/test_user/_users_test_user/post
   * @param string $site_id id del sitio donde se creará el usuario de prueba.
   */
  public function createTestUser($site_id = 'MCO')
  {
    // En SDK v3 usamos el cliente HTTP directamente
    $client = new MercadoPagoClient();
    $response = $client->send('POST', '/users/test_user', [
      'site_id' => $site_id
    ]);

    return $response;
  }

  /**
   * Obtiene el token de acceso desde la configuración
   * @return string
   */
  protected function getAccesToken()
  {
    return config('mercado-pago.access_token');
  }

  /**
   * Obtiene la URL de callback desde la configuración
   * @return string
   */
  protected function getCallbackUrl()
  {
    return config('mercado-pago.callback_url');
  }

  /**
   * Prepara los datos para las peticiones HTTP
   * @param array $data
   * @return array
   */
  protected function bodyHttp($data)
  {
    return [
      'json_data' => $data
    ];
  }
}
