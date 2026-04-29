# Checkout Pix Embutido — Responda Certo + Amplopay

## Arquivos

- `responda-certo-vendas-com-checkout-pix.html`  
  Página de vendas com popup de checkout Pix embutido.

- `api/create-pix.php`  
  Endpoint seguro que recebe os dados do comprador e chama a API da Amplopay.

- `api/config.php`  
  Configure suas chaves públicas/secretas e URLs.

- `api/amplopay-callback.php`  
  Webhook/callback para receber atualização de status da transação.

- `api/pix-status.php`  
  Endpoint simples para consultar status salvo localmente.

## Como instalar

1. Suba a página HTML na raiz do seu domínio.
2. Suba a pasta `api/` junto.
3. Edite `api/config.php`:
   - `public_key`
   - `secret_key`
   - `site_url`
   - `member_area_url`

4. Na página HTML, confirme que a constante está assim:

```js
const CHECKOUT_API_ENDPOINT = '/api/create-pix.php';
```

5. Configure no gateway a URL de callback, se necessário:

```text
https://seudominio.com/api/amplopay-callback.php
```

## Segurança

Nunca coloque `x-secret-key` no HTML ou JavaScript.
A chave secreta fica somente no servidor, dentro do PHP ou variável de ambiente.

## Importante

Este modelo gera Pix.  
Para cartão embutido, cuidado: a documentação de cartão exige dados sensíveis como número do cartão e CVV. O ideal é usar tokenização, checkout hospedado ou abordagem PCI-compliant.
