<?php

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PaymentBasic extends PaymentModule
{
    protected $_html;

    public function __construct()
    {
        $this->author    = 'Delvis Tovar';
        $this->name      = 'paymentbasic';
        $this->tab       = 'payment_gateways';
        $this->version   = '1.0.0';
        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('Payment Basic and API');
        $this->description = $this->l('Payment default and api Delvis Tovar');
    }

    public function install()
    {
        if (!parent::install() 
            || !$this->registerHook('paymentOptions')
            || !$this->registerHook('paymentReturn')
            || !$this->registerHook('actionFrontControllerSetMedia')
        ) {
            return false;
    }
    return true;
}

    /**
     * PS 17 
     * @param type $params
     * @return type
     */
    public function hookPaymentOptions($params) {

        if (!$this->active) {
            return;
        }

        $standardPayment = new PaymentOption();
        
        $inputs = [
            [
                'name' => 'custom_hidden_value',
                'type' => 'hidden',
                'value' => '30'
            ],
            [
                'name' => 'id_customer',
                'type' => 'hidden',
                'value' => $this->context->customer->id,
            ],
        ];
        $standardPayment->setModuleName($this->name)
        ->setLogo($this->context->link->getBaseLink().'/modules/paymentbasic/views/img/logo.png')
        ->setInputs($inputs)
        ->setCallToActionText($this->l('Payment Default DT'))
        ->setAction($this->context->link->getModuleLink($this->name, 'validation', array(), true))
        ->setAdditionalInformation($this->fetch('module:paymentbasic/views/templates/hook/displayPayment.tpl'));

        $this->smarty->assign(
            $this->getPaymentApiVars()
        );

        $apiPayement = new PaymentOption();
        $apiPayement->setModuleName($this->name)
        ->setCallToActionText($this->l('Payment Delvis Tovar (DT API)'))
                //Définition d'un formulaire personnalisé
        ->setForm($this->fetch('module:paymentbasic/views/templates/hook/payment_api_form.tpl'))
        ->setAdditionalInformation($this->fetch('module:paymentbasic/views/templates/hook/displayPaymentApi.tpl'));

        return [$standardPayment, $apiPayement];
    }

    /**
     * payment api
     * @return array
     */
    public function getPaymentApiVars()
    {
        return  [
           'payment_url' => Configuration::get('PAYMENT_API_URL'),
           'success_url' => Configuration::get('PAYMENT_API_URL_SUCESS'),
           'error_url' => Configuration::get('PAYMENT_API_URL_ERROR'),
           'id_cart' => $this->context->cart->id,
           'cart_total' =>  $this->context->cart->getOrderTotal(true, Cart::BOTH),
           'id_customer' => $this->context->cart->id_customer,
       ];
   }
   
    /**
     * @param type $params
     * @return type
     */
    public function hookDisplayPaymentReturn($params) 
    {
        if (!$this->active) {
            return;
        }
        
        $this->smarty->assign(
            $this->getTemplateVars()
        );
        return $this->fetch('module:paymentbasic/views/templates/hook/payment_return.tpl');
    }

    /**
     * Configuration admin module
     */
    public function getContent()
    {
        $this->_html .=$this->postProcess();
        $this->_html .= $this->renderForm();

        return $this->_html;

    }

    /**
     * @return type
     */
    public function postProcess()
    {
        if ( Tools::isSubmit('SubmitPaymentConfiguration'))
        {
            Configuration::updateValue('PAYMENT_API_URL', Tools::getValue('PAYMENT_API_URL'));
            Configuration::updateValue('PAYMENT_API_URL_SUCESS', Tools::getValue('PAYMENT_API_URL_SUCESS'));
            Configuration::updateValue('PAYMENT_API_URL_ERROR', Tools::getValue('PAYMENT_API_URL_ERROR'));
        }
        return $this->displayConfirmation($this->l('Configuration updated with success'));
    }

     /**
     * Form configuration admin
     */
     public function renderForm()
     {
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Payment Configuration'),
                    'icon' => 'icon-cogs'
                ],
                'description' => $this->l('Sample configuration form'),
                'input' => [
                 [
                    'type' => 'text',
                    'label' => $this->l('Payment api url'),
                    'name' => 'PAYMENT_API_URL',
                    'required' => true,
                    'empty_message' => $this->l('Please fill the payment api url'),
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Payment api success url'),
                    'name' => 'PAYMENT_API_URL_SUCESS',
                    'required' => true,
                    'empty_message' => $this->l('Please fill the payment api success url'),
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Payment api error url'),
                    'name' => 'PAYMENT_API_URL_ERROR',
                    'required' => true,
                    'empty_message' => $this->l('Please fill the payment api error url'),
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
                'class' => 'button btn btn-default pull-right',
            ],
        ],
    ];

    $helper = new HelperForm();
    $helper->show_toolbar = false;
    $helper->default_form_language = (int)Configuration::get('PS_LANG_DEFAULT');
    $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
    $helper->id = 'crdspayment';
    $helper->identifier = 'crdspayment';
    $helper->submit_action = 'SubmitPaymentConfiguration';
    $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
    $helper->token = Tools::getAdminTokenLite('AdminModules');
    $helper->tpl_vars = [
        'fields_value' => $this->getConfigFieldsValues(),
        'languages' => $this->context->controller->getLanguages(),
        'id_language' => $this->context->language->id
    ];

    return $helper->generateForm(array($fields_form));
}

    /**
     * 
     */
    public function getConfigFieldsValues()
    {
        return [
            'PAYMENT_API_URL' => Tools::getValue('PAYMENT_API_URL', Configuration::get('PAYMENT_API_URL')),
            'PAYMENT_API_URL_SUCESS' => Tools::getValue('PAYMENT_API_URL_SUCESS', Configuration::get('PAYMENT_API_URL_SUCESS')),
            'PAYMENT_API_URL_ERROR' => Tools::getValue('PAYMENT_API_URL_ERROR', Configuration::get('PAYMENT_API_URL_ERROR')),
        ];
    }


    /**
     * @return array
     */
    public function getTemplateVars()
    {
        return [
            'shop_name' => $this->context->shop->name,
            'custom_var' => $this->l('My custom var value'),
            'payment_details' => $this->l('custom details'),
        ];
    }

    public function hookActionFrontControllerSetMedia(){
        $this->context->controller->registerStylesheet(
            'mymodule-style',
            $this->_path.'views/css/paymentbasic.css',
            [
                'media' => 'all',
                'priority' => 1000,
            ]
        );
        /*$this->context->controller->registerJavascript(
            'mymodule-javascript',
            $this->_path.'views/js/crdspayment.js',
            [
                'position' => 'bottom',
                'priority' => 1000,
            ]
        );*/
    }

}