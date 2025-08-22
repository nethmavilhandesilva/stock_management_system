<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="background-color:#99ff99;">
      <form method="GET" action="{{ route('sales.report') }}">
        <div class="modal-header">
          <h5 class="modal-title" id="filterModalLabel">Filter Sales</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          {{-- Supplier Dropdown --}}
          <div class="mb-3">
            <label for="supplier_code" class="form-label">Supplier</label>
            <select name="supplier_code" id="supplier_code" class="form-select">
              <option value="">-- Select Supplier --</option>
              @foreach($suppliers as $supplier)
                <option value="{{ $supplier->code }}">{{ $supplier->code }} - {{ $supplier->name }}</option>
              @endforeach
            </select>
          </div>

          {{-- Item Dropdown --}}
          <div class="mb-3">
            <label for="item_code" class="form-label">Item</label>
            <select name="item_code" id="item_code" class="form-select">
              <option value="">-- Select Item --</option>
              @foreach($items as $item)
                <option value="{{ $item->no }}">{{ $item->no }} - {{ $item->type }}</option>
              @endforeach
            </select>
          </div>

        {{-- Customer Dropdown --}}
<div class="mb-3">
    <label for="filter_customer_code" class="form-label">පාරිභෝගික කේතය</label>
    <select name="customer_code" id="filter_customer_code" class="form-select form-select-sm select2-customer">
        <option value="">-- සියලුම පාරිභෝගිකයන් --</option>
        @php
            $customers = \App\Models\Sale::select('customer_code')->distinct()->get();
        @endphp
        @foreach($customers as $customer)
            <option value="{{ $customer->customer_code }}"
                {{ request('customer_code') == $customer->customer_code ? 'selected' : '' }}>
                {{ $customer->customer_code }}
            </option>
        @endforeach
    </select>
</div>


        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Filter</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>
