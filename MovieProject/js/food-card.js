let colddrinksdata = [
    {
        foodimg: "assets/food images/cocacola.png",
        foodname: "cocacola",
        price: "99",
    },
    {
        foodimg: "assets/food images/pepsi.png",
        foodname: "pepsi",
        price: "99",
    },
    {
        foodimg: "assets/food images/coca-fries-burger.png",
        foodname: "burgercombo",
        price: "249",
    },
    {
        foodimg: "assets/food images/fries.png",
        foodname: "fries",
        price: "119",
    },
    {
        foodimg: "assets/food images/coffee.png",
        foodname: "coffee",
        price: "79",
    },
    {
        foodimg: "assets/food images/tea.png",
        foodname: "tea",
        price: "69",
    },
    {
        foodimg: "assets/food images/coldcoffee.png",
        foodname: "coldcoffee",
        price: "129",
    }
]



let cartItems = [];


const updateCartUI = () => {
    const cartContainer = document.querySelector('.cart-items');
    const totalContainer = document.querySelector('.total-amount');

    cartContainer.innerHTML = ''; 
    let totalAmount = 0; 

    cartItems.forEach(item => {
        const cartItem = document.createElement('div');
        cartItem.classList.add('cart-item');
        const subtotal = item.price * item.quantity; 
        totalAmount += subtotal;
        cartItem.innerHTML = `
           <div class="cart-item-individual">
           <p>${item.foodname} - ${item.price} x ${item.quantity} = ${subtotal}</p>
           <button class="remove-item"><i class="fa-solid fa-trash"></i></button>
           </div>
        `;
        cartContainer.appendChild(cartItem);

        cartItem.querySelector('.remove-item').addEventListener('click', () => {
            removeItemFromCart(item);
        });
    });

    if (cartItems.length > 0) {
        totalContainer.textContent = `Total: ${totalAmount}`;
    } else {
        totalContainer.textContent = ''; 
    }
};

const removeItemFromCart = (itemToRemove) => {
    cartItems = cartItems.filter(item => item !== itemToRemove); 
    updateCartUI(); 
};

const addToCart = (foodItem) => {
    const existingItem = cartItems.find(item => item.foodname === foodItem.foodname);
    if (existingItem) {
        existingItem.quantity++;
    } else {
        foodItem.quantity = 1;
        foodItem.subtotal = foodItem.price;
        cartItems.push(foodItem);
    }
    updateCartUI(); 
};


document.addEventListener('DOMContentLoaded', () => {
    const cardcon = document.querySelector('.colddrinks');

    const postmethod = () => {
        colddrinksdata.forEach((data2) => {
            const poseele = document.createElement('div');
            poseele.className = 'col-6 col-sm-6  col-lg-3 col-md-4 col-xl-3';
            poseele.innerHTML += `
                <div class="card">
                    <img src="${data2.foodimg}" class="card-img-top" alt="...">
                    <div class="card-body">
                        <div class="food-name-price">
                            <p>${data2.foodname}</p>
                            <p><i class="fa-sharp fa-solid fa-indian-rupee-sign"></i>${data2.price}</p>
                        </div>
                        <div class="add-food">
                            <button class="food-btn"><i class="fa-solid fa-paper-plane"></i>add</button>
                        </div>
                    </div>
                </div>`;

            poseele.querySelector('.add-food .food-btn').addEventListener('click', () => {
                addToCart(data2); 
            });

            cardcon.appendChild(poseele);
        });
    };

    postmethod();


});

document.getElementById('continueButton').addEventListener('click', () => {
    localStorage.setItem('selectedFoodItems', JSON.stringify(cartItems));

    let totalAmount = 0;
    cartItems.forEach(item => {
        totalAmount += item.price * item.quantity;
    });

    let discount = 0;
    if (totalAmount >= 1000) {
        discount = 0.05 * totalAmount; 
    }

    let totalAfterDiscount = totalAmount - discount;

    localStorage.setItem('totalAmount', totalAfterDiscount);

    window.location.href = 'parking.html';
});
