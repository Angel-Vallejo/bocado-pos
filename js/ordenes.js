// /js/ordenes.js
function verDetalleOrden(btn) {
    const orden = JSON.parse(btn.dataset.orden);
    document.getElementById('modalOrdenFolio').textContent = `Detalle del Pedido #${orden.folio}`;
    document.getElementById('modalOrdenMeta').textContent = `${orden.hora} • ${orden.mesa} • Cobró: ${orden.cajero} (${orden.pago})`;
    document.getElementById('modalOrdenTotal').textContent = `$${orden.total}`;

    const lista = document.getElementById('modalOrdenItems');
    lista.innerHTML = orden.items.map(item => `
        <li class="list-group-item bg-transparent d-flex justify-content-between align-items-start px-0">
            <div>
                <span class="font-weight-bold">${item.cantidad}x ${item.producto}</span>
                ${item.personalizacion ? `<small class="text-warning d-block">${item.personalizacion}</small>` : ''}
            </div>
            <span class="font-weight-bold text-success">$${parseFloat(item.subtotal).toFixed(2)}</span>
        </li>
    `).join('');

    document.getElementById('modalDetalleOrden').classList.remove('d-none');
}

function cerrarModalOrden() {
    document.getElementById('modalDetalleOrden').classList.add('d-none');
}