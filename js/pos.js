// /js/pos.js
const $ = id => document.getElementById(id);
let carrito = [];
let prodActual = null;

function filtrarProductos() {
    const txt = $('inputBuscar').value.toLowerCase().trim();
    const cat = $('selectCategoria').value;
    document.querySelectorAll('.item-producto').forEach(card => {
        const ok = card.dataset.nombre.includes(txt) && (cat === 'Todas' || card.dataset.categoria === cat);
        card.classList.toggle('d-none', !ok);
    });
}

function seleccionarProducto(el) {
    const p = JSON.parse(el.dataset.prod);
    if (p.categoria !== 'Hamburguesas') {
        return agregarAlCarrito({ productoID: p.id, nombre: p.nombre, precioUnitario: p.precio, personalizacion: '', extras: [] });
    }

    prodActual = { ...p, lista: p.ingredientes ? p.ingredientes.split(',').map(s => s.trim()).filter(Boolean) : [] };
    $('modalProdTitulo').textContent = p.nombre;
    $('contenedorIngredientesBase').innerHTML = prodActual.lista.length
        ? prodActual.lista.map((ing, i) => `
            <div class="col-6 mb-1">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input chk-ing" id="ing_${i}" value="${ing}" checked>
                    <label class="custom-control-label" for="ing_${i}">${ing}</label>
                </div>
            </div>`).join('')
        : '<div class="col-12"><small class="text-muted">Sin ingredientes base.</small></div>';

    document.querySelectorAll('.chk-extra-modal').forEach(c => c.checked = false);
    recalcularModal();
    $('modalPersonalizar').classList.remove('d-none');
}

function recalcularModal() {
    if (!prodActual) return;
    let total = prodActual.precio;
    document.querySelectorAll('.chk-extra-modal:checked').forEach(c => total += parseFloat(c.dataset.precio));
    $('modalPrecioTotal').textContent = '$' + total.toFixed(2);
}

function confirmarHamburguesa() {
    if (!prodActual) return;
    const quitados = [...document.querySelectorAll('.chk-ing:not(:checked)')].map(c => 'Sin ' + c.value);
    let precioFinal = prodActual.precio;
    const extras = [];

    document.querySelectorAll('.chk-extra-modal:checked').forEach(c => {
        const precio = parseFloat(c.dataset.precio);
        precioFinal += precio;
        extras.push({ extraID: parseInt(c.value), nombre: c.dataset.nombre, precio });
    });

    const notas = [...quitados, ...extras.map(e => '+' + e.nombre)].join(', ');
    agregarAlCarrito({ productoID: prodActual.id, nombre: prodActual.nombre, precioUnitario: precioFinal, personalizacion: notas, extras });
    cerrarModal('modalPersonalizar');
}

function agregarAlCarrito(item) {
    const existe = carrito.find(c => c.productoID === item.productoID && c.personalizacion === item.personalizacion);
    existe ? existe.cantidad++ : carrito.push({ ...item, cantidad: 1 });
    renderCarrito();
}

function cambiarCantidad(idx, delta) {
    carrito[idx].cantidad += delta;
    if (carrito[idx].cantidad <= 0) carrito.splice(idx, 1);
    renderCarrito();
}

function vaciarCarrito() {
    carrito = [];
    renderCarrito();
}

function renderCarrito() {
    const lista = $('listaCarrito');
    if (!carrito.length) {
        lista.innerHTML = '<li class="list-group-item bg-transparent text-center text-muted py-5">Selecciona productos del menú.</li>';
        $('txtSubtotal').textContent = $('txtTotal').textContent = '$0.00';
        $('contadorArticulos').textContent = '0 artículos';
        $('btnAbrirCobro').disabled = true;
        return;
    }

    let total = 0, cant = 0;
    lista.innerHTML = carrito.map((item, i) => {
        const sub = item.precioUnitario * item.cantidad;
        total += sub; cant += item.cantidad;
        return `
            <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0">
                <div class="pr-2">
                    <h6 class="mb-0 font-weight-bold">${item.nombre}</h6>
                    ${item.personalizacion ? `<small class="text-warning d-block">${item.personalizacion}</small>` : ''}
                    <small class="text-muted">$${item.precioUnitario.toFixed(2)} c/u</small>
                </div>
                <div class="d-flex align-items-center">
                    <button type="button" class="btn btn-xs btn-outline-secondary px-2 py-0" onclick="cambiarCantidad(${i}, -1)">-</button>
                    <span class="mx-2 font-weight-bold">${item.cantidad}</span>
                    <button type="button" class="btn btn-xs btn-outline-secondary px-2 py-0 mr-3" onclick="cambiarCantidad(${i}, 1)">+</button>
                    <span class="font-weight-bold text-success">$${sub.toFixed(2)}</span>
                </div>
            </li>`;
    }).join('');

    $('txtSubtotal').textContent = $('txtTotal').textContent = '$' + total.toFixed(2);
    $('contadorArticulos').textContent = `${cant} ${cant === 1 ? 'artículo' : 'artículos'}`;
    $('btnAbrirCobro').disabled = false;
}

function abrirModalCobro() {
    const total = carrito.reduce((acc, i) => acc + i.precioUnitario * i.cantidad, 0);
    $('cobroTotalDisplay').textContent = '$' + total.toFixed(2);
    $('inputCarritoJson').value = JSON.stringify(carrito);
    $('inputEfectivoRecibido').value = '';
    $('txtCambio').textContent = '$0.00';
    $('modalCobro').classList.remove('d-none');
}

function cerrarModal(id) { $(id).classList.add('d-none'); }

function toggleCalculadoraCambio() {
    $('bloqueEfectivo').classList.toggle('d-none', $('selectFormaPago').value !== 'Efectivo');
}

function calcularCambio() {
    const total = carrito.reduce((acc, i) => acc + i.precioUnitario * i.cantidad, 0);
    const cambio = (parseFloat($('inputEfectivoRecibido').value) || 0) - total;
    $('txtCambio').textContent = cambio >= 0 ? '$' + cambio.toFixed(2) : 'Falta dinero';
}