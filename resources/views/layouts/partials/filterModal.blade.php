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
            <label for="customer_short_name" class="form-label">Customer</label>
            <select name="customer_short_name" id="customer_short_name" class="form-select">
              <option value="">-- Select Customer --</option>
              @foreach($customers as $customer)
                <option value="{{ $customer->short_name }}">{{ $customer->short_name }} - {{ $customer->name }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Filter</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>
