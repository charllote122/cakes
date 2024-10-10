index.js
const express = require('express');
const bodyParser = require('body-parser');
const mpesa = require('mpesa-node'); // M-Pesa API wrapper (make sure you have this package installed)

const app = express();
app.use(bodyParser.json());

const mpesaConfig = {
    consumerKey: 'OpAEKPvyFH0oEOPIE3NBAjaj8gikND2EIWiLoXITfSFqImJf',
    consumerSecret: '85pjjTxDQs8KScHFuwGaxLm0EbYGjyulosUBbjJCvP1sZG91XaE2IZj8txRRky9h',
    shortcode: '174379',
    lipaNaMpesaOnlinePasskey: 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919',
    lipaNaMpesaOnlineShortcode: 'your_shortcode',
    baseUrl: 'https://sandbox.safaricom.co.ke/',  // Change to live URL in production
};

const mpesaInstance = new mpesa(mpesaConfig);

// Endpoint to handle M-Pesa payment
app.post('/mpesa/pay', async (req, res) => {
    const { amount } = req.body;

    try {
        const result = await mpesaInstance.lipaNaMpesaOnline({
            amount,
            phoneNumber: '2547XXXXXXXX', // Customer phone number
            accountReference: 'Order1234',
            transactionDesc: 'Payment for Order',
            callbackUrl: 'https://your-backend-server.com/mpesa/callback',
        });

        res.json({ success: true, result });
    } catch (error) {
        res.json({ success: false, error });
    }
});

// Callback endpoint (for handling payment results)
app.post('/mpesa/callback', (req, res) => {
    // Handle M-Pesa payment callback here
    console.log('M-Pesa Payment Callback:', req.body);
    res.sendStatus(200);
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
    console.log(`Server running on port ${PORT}`);
});
