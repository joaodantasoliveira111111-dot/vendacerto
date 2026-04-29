export default async function handler(req, res) {
  if (req.method !== 'GET') {
    return res.status(405).json({ success: false, message: 'Method Not Allowed' });
  }

  const { id } = req.query;

  if (!id) {
    return res.status(400).json({ success: false, message: 'Missing transaction id' });
  }

  const publicKey = process.env.AMPLOPAY_PUBLIC_KEY;
  const secretKey = process.env.AMPLOPAY_SECRET_KEY;

  if (!publicKey || !secretKey) {
    return res.status(500).json({ success: false, message: 'Chaves da Amplopay não configuradas.' });
  }

  try {
    const response = await fetch(`https://app.amplopay.com/api/v1/gateway/transactions?id=${id}`, {
      method: 'GET',
      headers: {
        'x-public-key': publicKey,
        'x-secret-key': secretKey
      }
    });

    const data = await response.json();

    if (!response.ok) {
      return res.status(response.status).json({ success: false, message: data.message || 'Erro ao consultar transação.' });
    }

    return res.status(200).json({
      success: true,
      id: data.id,
      status: data.status // PENDING, COMPLETED, FAILED, etc.
    });

  } catch (error) {
    return res.status(502).json({ success: false, message: 'Erro de comunicação com a Amplopay.', details: error.message });
  }
}
