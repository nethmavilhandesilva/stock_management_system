@extends('layouts.app')

@section('content')
<style>
    body {
        background-color: #99ff99;
    }
</style>

<div class="container mt-4">

    <h3>GRN Entries</h3>

    {{-- Show success message --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Form --}}
    <form method="POST" action="{{ route('grn.store3') }}">
        @csrf
        <div class="row g-3 mb-3">

            {{-- Code Dropdown --}}
            <div class="col-md-3">
                <label for="nc_code" class="form-label">Code</label>
                <select id="nc_code" name="code" class="form-control form-control-sm">
                    <option value="" disabled selected>-- Select Code --</option>
                    @foreach($notChangingGRNs as $grn)
                        <option value="{{ $grn->id }}" 
                            data-item-code="{{ $grn->item_code }}" 
                            data-item-name="{{ $grn->item_name }}"
                            data-grn-no="{{ $grn->grn_no }}">
                            {{ $grn->code }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Item (auto-filled) --}}
            <div class="col-md-3">
                <label for="nc_item" class="form-label">Item</label>
                <input type="text" id="nc_item" name="item_info" class="form-control form-control-sm" readonly>
            </div>

            {{-- Packs --}}
            <div class="col-md-2">
                <label for="nc_packs" class="form-label">Packs</label>
                <input type="number" id="nc_packs" name="packs" class="form-control form-control-sm" min="1">
            </div>

            {{-- Weight --}}
            <div class="col-md-2">
                <label for="nc_weight" class="form-label">Weight (kg)</label>
                <input type="number" id="nc_weight" name="weight" class="form-control form-control-sm" step="0.01">
            </div>

            {{-- GRN No (auto-filled) --}}
            <div class="col-md-2">
                <label for="nc_grn_no" class="form-label">GRN No</label>
                <input type="text" id="nc_grn_no" name="grn_no" class="form-control form-control-sm">
            </div>

            {{-- Submit --}}
            <div class="col-12 d-flex gap-2 mt-2">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="material-icons align-middle me-1">check_circle</i> Update GRN
                </button>

                <a href="{{ route('grn.create') }}" class="btn btn-secondary btn-sm">
                    <i class="material-icons align-middle me-1">add_circle</i> New GRN
                </a>
            </div>
        </div>
    </form>

    {{-- Table --}}
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Code</th>
                <th>Supplier Code</th>
                <th>Item Code</th>
                <th>Item Name</th>
                <th>Packs</th>
                <th>Weight (kg)</th>
                <th>Txn Date</th>
                <th>GRN No</th>
            </tr>
        </thead>
        <tbody>
            @foreach($grnEntries as $entry)
                <tr>
                    <td>{{ $entry->id }}</td>
                    <td>{{ $entry->code }}</td>
                    <td>{{ $entry->supplier_code }}</td>
                    <td>{{ $entry->item_code }}</td>
                    <td>{{ $entry->item_name }}</td>
                    <td>{{ $entry->packs }}</td>
                    <td>{{ $entry->weight }}</td>
                    <td>{{ $entry->txn_date }}</td>
                    <td>{{ $entry->grn_no }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Auto-fill item and GRN No --}}
<script>
    const codeSelect = document.getElementById('nc_code');
    const itemInput = document.getElementById('nc_item');
    const ncGrnNo = document.getElementById('nc_grn_no');

    codeSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        itemInput.value = selectedOption.dataset.itemName || '';
        ncGrnNo.value = selectedOption.dataset.grnNo || '';
    });
</script>
@endsection
