@extends('farmacia.layout')

@section('title', 'Farmacia - Punto de Venta')
@section('nav-pos', 'active')

@section('content')
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-top: 1.5rem;">
    
    <div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 style="font-weight: 800; color: #1E1A17;">
                <i class="fas fa-pills" style="color: #F05A4E; margin-right: 0.5rem;"></i> Inventario Venta Directa
            </h3>
            <div style="position: relative; width: 250px;">
                <input type="text" id="searchMed" onkeyup="filterMeds()" placeholder="Buscar..." style="width: 100%; padding: 0.6rem 1rem 0.6rem 2.2rem; border: 1px solid #E5E7EB; border-radius: 20px; font-size: 0.85rem;">
                <i class="fas fa-search" style="position: absolute; left: 0.8rem; top: 50%; transform: translateY(-50%); color: #736860;"></i>
            </div>
        </div>
        
        <div id="medGrid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
            @foreach($medicamentos as $med)
                @php $isControlled = ($med->required_level == 'A' || strtolower($med->type) == 'controlado'); @endphp
                <div class="med-card" data-name="{{ strtolower($med->name) }}" 
                     @if(!$isControlled) onclick="addToCart({{ $med->id }}, '{{ $med->name }}', {{ $med->venta_price }}, {{ $med->stock }})" @endif
                     style="background: #F9FAFB; padding: 1rem; border-radius: 8px; cursor: {{ $isControlled ? 'not-allowed' : 'pointer' }}; border: 2px solid {{ $isControlled ? '#FECACA' : 'transparent' }}; opacity: {{ $isControlled ? '0.7' : '1' }};">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="font-weight: 700; color: #1E1A17; font-size: 0.9rem;">{{ $med->name }}</div>
                        @if($isControlled) <i class="fas fa-lock" style="color: #DC2626; font-size: 0.8rem;"></i> @endif
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-top: 0.5rem;">
                        @if($isControlled)
                            <span style="color: #DC2626; font-weight: 700; font-size: 0.75rem;">REQUIERE RECETA</span>
                        @else
                            <span style="color: #2D9E6A; font-weight: 700; font-size: 0.9rem;">$ {{ number_format($med->venta_price, 2) }}</span>
                        @endif
                        <span style="color: #736860; font-size: 0.75rem;">Stock: {{ $med->stock }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.04); position: sticky; top: 2rem; height: fit-content;">
        <h3 style="font-weight: 800; margin-bottom: 1.5rem; color: #1E1A17;">
            <i class="fas fa-shopping-cart" style="color: #FF8C42; margin-right: 0.5rem;"></i> Venta Directa
        </h3>

        @if(session('success'))
            <div style="background: #D1FAE5; color: #2D9E6A; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; font-weight: 600;">
                <i class="fas fa-check-circle"></i> {{ session('success') }}<br>
                <a href="{{ route('farmacia.pos.ticket', session('ticket_id')) }}" target="_blank" style="color: #1E1A17; font-weight: 800; text-decoration: underline; display: block; margin-top: 0.5rem;">
                    <i class="fas fa-print"></i> Imprimir Ticket
                </a>
            </div>
        @endif

        @if($errors->any())
            <div style="background: #FFF0F0; color: #C7291C; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; border: 1px solid #FECACA;">
                <i class="fas fa-exclamation-triangle"></i> {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('farmacia.pos.venta') }}" method="POST">
            @csrf
            <div id="cart" style="margin-bottom: 1rem; min-height: 200px;">
                <div id="empty-cart" style="text-align: center; color: #736860; padding: 2rem 0;">
                    <i class="fas fa-mouse-pointer" style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"></i>
                    Haz clic en un medicamento.
                </div>
            </div>

            <div id="cart-summary" style="display: none; border-top: 2px dashed #E5E7EB; padding-top: 1rem;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                    <span style="color: #736860;">Total:</span>
                    <span id="total" style="font-weight: 800; font-size: 1.5rem; color: #1E1A17;">$0.00</span>
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="font-size: 0.8rem; color: #736860; font-weight: 700;">Método de Pago</label>
                    <select name="payment_method" style="width: 100%; padding: 0.8rem; border: 1px solid #E5E7EB; border-radius: 8px; margin-top: 0.3rem;" required>
                        <option value="efectivo">Efectivo</option>
                        <option value="tarjeta">Tarjeta</option>
                        <option value="transferencia">Transferencia</option>
                    </select>
                </div>
                <button type="submit" style="width: 100%; background: #1E1A17; color: white; padding: 1rem; border-radius: 8px; font-weight: 700; border: none; cursor: pointer;">
                    <i class="fas fa-cash-register"></i> Cobrar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let cartItems = [];
    function filterMeds() {
        let filter = document.getElementById('searchMed').value.toLowerCase();
        let cards = document.getElementsByClassName('med-card');
        for (let i = 0; i < cards.length; i++) {
            cards[i].style.display = cards[i].getAttribute('data-name').includes(filter) ? "" : "none";
        }
    }
    function addToCart(id, name, price, stock) {
        let existing = cartItems.find(item => item.id === id);
        if (existing) { if (existing.quantity < stock) existing.quantity++; else alert('Stock máximo'); }
        else cartItems.push({ id, name, price, stock, quantity: 1 });
        renderCart();
    }
    function removeFromCart(id) { cartItems = cartItems.filter(item => item.id !== id); renderCart(); }
    function changeQty(id, delta) {
        let item = cartItems.find(i => i.id === id);
        if (!item) return;
        let newQty = item.quantity + delta;
        if (newQty <= 0) removeFromCart(id);
        else if (newQty <= item.stock) { item.quantity = newQty; renderCart(); }
        else alert('Stock máximo');
    }
    function renderCart() {
        const cartDiv = document.getElementById('cart');
        const summaryDiv = document.getElementById('cart-summary');
        if (cartItems.length === 0) {
            cartDiv.innerHTML = '<div id="empty-cart" style="text-align: center; color: #736860; padding: 2rem 0;"><i class="fas fa-mouse-pointer" style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"></i>Haz clic en un medicamento.</div>';
            summaryDiv.style.display = 'none'; return;
        }
        summaryDiv.style.display = 'block';
        let subtotal = 0; let html = '';
        cartItems.forEach((item, index) => {
            let lineTotal = item.price * item.quantity; subtotal += lineTotal;
            html += `<div style="background: #F9FAFB; padding: 0.8rem; border-radius: 8px; margin-bottom: 0.5rem; border: 1px solid #E5E7EB;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                    <span style="font-weight: 600; font-size: 0.85rem; color: #1E1A17;">${item.name}</span>
                    <span style="font-size: 0.8rem; color: #736860; font-weight: 700;">$${lineTotal.toFixed(2)}</span>
                </div>
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <input type="hidden" name="items[${index}][id]" value="${item.id}">
                        <input type="hidden" name="items[${index}][quantity]" value="${item.quantity}">
                        <button type="button" onclick="changeQty(${item.id}, -1)" style="background: white; border: 1px solid #E5E7EB; width: 30px; height: 30px; border-radius: 6px; cursor: pointer; font-weight: bold; color: #C7291C;">-</button>
                        <span style="font-weight: 800; font-size: 1rem; min-width: 20px; text-align: center;">${item.quantity}</span>
                        <button type="button" onclick="changeQty(${item.id}, 1)" style="background: white; border: 1px solid #E5E7EB; width: 30px; height: 30px; border-radius: 6px; cursor: pointer; font-weight: bold; color: #2D9E6A;">+</button>
                    </div>
                    <button type="button" onclick="removeFromCart(${item.id})" style="color: #C7291C; background: none; border: none; cursor: pointer; font-size: 1rem;"><i class="fas fa-trash"></i></button>
                </div>
            </div>`;
        });
        cartDiv.innerHTML = html;
        document.getElementById('total').innerText = `$${subtotal.toFixed(2)}`;
    }
</script>
@endsection
