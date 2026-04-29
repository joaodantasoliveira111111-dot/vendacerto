<?php
/**
 * CONFIGURAÇÃO DO CHECKOUT PIX — AMPLopay
 *
 * IMPORTANTE:
 * 1. Nunca coloque sua x-secret-key no HTML/JavaScript.
 * 2. Configure estas variáveis no ambiente do servidor, se possível.
 * 3. Em hospedagem simples, você pode preencher abaixo, mas mantenha este arquivo fora de repositório público.
 */

return [
    'public_key' => getenv('AMPLOPAY_PUBLIC_KEY') ?: 'COLOQUE_SUA_PUBLIC_KEY_AQUI',
    'secret_key' => getenv('AMPLOPAY_SECRET_KEY') ?: 'COLOQUE_SUA_SECRET_KEY_AQUI',

    'pix_endpoint' => 'https://app.amplopay.com/api/v1/gateway/pix/receive',

    'product_id' => 'responda-certo',
    'product_name' => 'Responda Certo',
    'amount' => 37.00,

    // Use a URL pública real do seu domínio em produção.
    // Ex.: https://seudominio.com
    'site_url' => getenv('SITE_URL') ?: 'https://seudominio.com',

    // Para onde mandar o cliente depois do pagamento aprovado.
    'member_area_url' => getenv('MEMBER_AREA_URL') ?: 'https://seudominio.com/area-membro.html',
];
