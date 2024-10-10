app.js
// Simulated product list (normally you'd fetch from a database)
const products = [
    { id: 1, name: 'Product 1', price: 100 },
    { id: 2, name: 'Product 2', price: 200 },
    { id: 3, name: 'Product 3', price: 300 },
];

// Function to render products
function displayProducts() {
    const productList = document.getElementById('product-list');
    products.forEach(product => {
        const productDiv = document.createElement('div');
        productDiv.classList.add('product-item');
        productDiv.innerHTML = `
            <h3>${product.name}</h3>
            <p>Price: ${product.price}</p>
            <button onclick="addToCart(${product.id})">Add to Cart</button>
        `;
        productList.appendChild(productDiv);
    });
}

// Cart functionality
let cart = [];
function addToCart(productId) {
    const product = products.find(p => p.id === productId);
    cart.push(product);
    displayCart();
}

function displayCart() {
    const cartItems = document.getElementById('cart-items');
    cartItems.innerHTML = '';
    cart.forEach(item => {
        const listItem = document.createElement('li');
        listItem.textContent = `${item.name} - ${item.price}`;
        cartItems.appendChild(listItem);
    });
}

// Handle checkout (M-Pesa API integration)
document.getElementById('checkout').addEventListener('click', async () => {
    const totalAmount = cart.reduce((sum, item) => sum + item.price, 0);
    alert(`Total amount to pay: ${totalAmount}`);
    
    // Call backend to initiate M-Pesa payment
    const response = await fetch('https://your-backend-server.com/mpesa/pay', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ amount: totalAmount }),
    });

    const result = await response.json();
    if (result.success) {
        alert('Payment successful!');
    } else {
        alert('Payment failed!');
    }
});

displayProducts();
