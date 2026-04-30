export default async function handler(req, res) {
  if (req.method !== 'POST') {
    return res.status(405).json({ success: false, message: 'Method Not Allowed' });
  }

  const { name, email, phone, document } = req.body || {};

  if (!name || !email || !phone || !document) {
    return res.status(400).json({ success: false, message: 'Preencha todos os campos.' });
  }

  const publicKey = process.env.AMPLOPAY_PUBLIC_KEY;
  const secretKey = process.env.AMPLOPAY_SECRET_KEY;
  const siteUrl = process.env.SITE_URL || 'https://seudominio.com';

  if (!publicKey || !secretKey) {
    return res.status(500).json({ success: false, message: 'Chaves da Amplopay não configuradas nas variáveis de ambiente.' });
  }

  const identifier = 'rc_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5);

  const payload = {
    identifier,
    amount: 37.00, // Valor fixo do produto
    client: { name, email, phone, document },
    dueDate: new Date(Date.now() + 86400000).toISOString().split('T')[0],
    metadata: {
      provider: 'Checkout Embutido Vercel',
      product: 'responda-certo'
    },
    callbackUrl: `${siteUrl}/api/amplopay-callback`
  };

  try {
    const response = await fetch('https://app.amplopay.com/api/v1/gateway/pix/receive', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'x-public-key': publicKey,
        'x-secret-key': secretKey
      },
      body: JSON.stringify(payload)
    });

    const data = await response.json();

    if (!response.ok) {
      return res.status(response.status).json({
        success: false,
        message: data.message || data.errorDescription || 'Erro ao gerar cobrança Pix.'
      });
    }

    return res.status(200).json({
      success: true,
      transactionId: data.id || data.transactionId, // O id da transação na Amplopay
      status: data.status,
      pix: data.pix,
      email: email // passamos de volta para o front-end
    });

  } catch (error) {
    return res.status(502).json({ success: false, message: 'Erro de comunicação com a Amplopay.', details: error.message });
  }
}
