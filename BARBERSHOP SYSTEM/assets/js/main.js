// basic helpers
function rm(cents){ return 'RM ' + (cents/100).toFixed(2); }

// AJAX utility functions
function ajaxPost(url, data, callback) {
  const xhr = new XMLHttpRequest();
  xhr.open('POST', url, true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  xhr.onreadystatechange = function() {
    if (xhr.readyState === 4) {
      if (xhr.status === 200) {
        callback(null, xhr.responseText);
      } else {
        callback(xhr.status, xhr.responseText);
      }
    }
  };
  const params = new URLSearchParams(data).toString();
  xhr.send(params);
}

function ajaxGet(url, callback) {
  const xhr = new XMLHttpRequest();
  xhr.open('GET', url, true);
  xhr.onreadystatechange = function() {
    if (xhr.readyState === 4) {
      if (xhr.status === 200) {
        callback(null, xhr.responseText);
      } else {
        callback(xhr.status, xhr.responseText);
      }
    }
  };
  xhr.send();
}

// Show loading state
function showLoading(element) {
  element.classList.add('loading');
}

function hideLoading(element) {
  element.classList.remove('loading');
}

// Show alert message
function showAlert(message, type = 'success') {
  const alertDiv = document.createElement('div');
  alertDiv.className = `alert alert-${type}`;
  alertDiv.textContent = message;
  document.body.appendChild(alertDiv);
  setTimeout(() => alertDiv.remove(), 3000);
}

// Form validation
function validateForm(form) {
  const inputs = form.querySelectorAll('input[required], select[required]');
  for (let input of inputs) {
    if (!input.value.trim()) {
      showAlert('Please fill in all required fields.', 'error');
      input.focus();
      return false;
    }
  }
  return true;
}

// Cart management (for POS)
let cart = [];

function addToCart(item) {
  const existing = cart.find(i => i.id === item.id && i.type === item.type);
  if (existing) {
    existing.qty += 1;
  } else {
    cart.push({ ...item, qty: 1 });
  }
  updateCartDisplay();
}

function updateCartDisplay() {
  const cartTable = document.querySelector('#cart-table tbody');
  if (!cartTable) return;
  cartTable.innerHTML = '';
  let total = 0;
  cart.forEach(item => {
    const lineTotal = item.qty * item.price;
    total += lineTotal;
    const row = `<tr><td>${item.name}</td><td>${item.qty}</td><td>${rm(lineTotal)}</td></tr>`;
    cartTable.innerHTML += row;
  });
  document.querySelector('#cart-total').textContent = rm(total);
}
