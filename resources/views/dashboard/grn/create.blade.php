@extends('layouts.app')

@section('content')
    {{-- Material Icons --}}
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <style>
        body {
            background-color: #99ff99;
        }

        .custom-card {
            background-color: #006400 !important;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            padding: 24px;
        }

        .form-label {
            font-weight: bold;
            color: black;
        }

        input[readonly] {
            background-color: #e9ecef;
        }

        /* Buttons */
        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }
        .btn-info {
            background-color: #0dcaf0;
            border-color: #0dcaf0;
            color: #fff;
        }
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        .btn-sm {
            font-size: 0.75rem;
            padding: 4px 8px;
        }

        /* Table */
        .table thead th {
            background-color: #e6f0ff;
            color: #003366;
            text-align: center;
        }
        .table tbody td {
            vertical-align: middle;
            text-align: center;
        }
        .table-hover tbody tr:hover {
            background-color: #f1f5ff;
        }
        #entriesTable {
            font-size: 0.85rem;
        }
        #entriesTable th,
        #entriesTable td {
            padding: 0.3rem 0.5rem;
        }
        #entriesTable thead th {
            font-weight: 600;
        }

        /* Hidden columns initially */
        .total-grn-column,
        .total-grn-header,
        .per-kg-price-column,
        .per-kg-price-header {
            display: none;
        }

        /* Search input */
        #searchInput {
            max-width: 400px;
            margin-bottom: 1rem;
        }

        /* Context menu */
        .custom-context-menu {
            position: absolute;
            z-index: 9999;
            background-color: white;
            border: 1px solid #ccc;
            padding: 5px 0;
            box-shadow: 2px 2px 6px rgba(0, 0, 0, 0.2);
            display: none;
            width: 140px;
        }
        .custom-context-menu li {
            list-style: none;
            padding: 8px 12px;
            cursor: pointer;
        }
        .custom-context-menu li:hover {
            background-color: #f1f1f1;
        }
    </style>

   <div class="container-fluid mt-7">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="custom-card">
                <h2 class="text-primary mb-4 text-center">📝 නව GRN ඇතුළත් කිරීම & GRN ලැයිස්තුව</h2>

                <form method="POST" action="{{ route('grn.store2') }}">
                    @csrf
                    <div class="row g-3">
                        {{-- Item --}}
                        <div class="col-md-3">
                            <label for="item_code" class="form-label">අයිතම <span class="text-danger">*</span></label>
                            <select id="item_code" name="item_code" class="form-control form-control-sm @error('item_code') is-invalid @enderror" required>
                                <option value="" disabled selected>-- Select --</option>
                                @foreach($items as $item)
                                    <option value="{{ $item->no }}" data-type="{{ $item->type }}" {{ old('item_code') == $item->no ? 'selected' : '' }}>
                                        {{ $item->no }} - {{ $item->type }}
                                    </option>
                                @endforeach
                            </select>
                            @error('item_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Supplier --}}
                        <div class="col-md-3">
                            <label for="supplier_name" class="form-label">සැපයුම්කරු <span class="text-danger">*</span></label>
                            <input list="suppliers_list" id="supplier_name" name="supplier_name" class="form-control form-control-sm @error('supplier_name') is-invalid @enderror" value="{{ old('supplier_name') }}" required oninput="this.value = this.value.toUpperCase();" style="text-transform: uppercase;">
                            <datalist id="suppliers_list">
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->code }}" data-code="{{ $supplier->code }}">
                                @endforeach
                            </datalist>
                            <input type="hidden" id="supplier_code_input" name="supplier_code">
                            @error('supplier_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- GRN No --}}
                        <div class="col-md-3">
                            <label for="grn_no" class="form-label">GRN අංකය</label>
                            <input type="text" id="grn_no" name="grn_no" class="form-control form-control-sm" value="{{ old('grn_no') }}">
                        </div>

                        {{-- Warehouse --}}
                        <div class="col-md-3">
                            <label for="warehouse_no" class="form-label">ගබඩා අංකය</label>
                            <input type="text" id="warehouse_no" name="warehouse_no" class="form-control form-control-sm" value="{{ old('warehouse_no') }}" >
                        </div>

                        {{-- Packs --}}
                        <div class="col-md-2">
                            <label for="packs" class="form-label">පැක්‌ <span class="text-danger">*</span></label>
                            <input type="number" id="packs" name="packs" class="form-control form-control-sm" value="{{ old('packs') }}" min="1">
                        </div>

                        {{-- Weight --}}
                        <div class="col-md-2">
                            <label for="weight" class="form-label">බර (kg) <span class="text-danger">*</span></label>
                            <input type="number" id="weight" name="weight" class="form-control form-control-sm" value="{{ old('weight') }}" step="0.01" min="0.01">
                        </div>

                        {{-- Total GRN --}}
                        <div class="col-md-3">
                            <label for="total_grn" class="form-label">GRN මුළු එකතුව</label>
                            <input type="number" id="total_grn" name="total_grn" class="form-control form-control-sm" value="{{ old('total_grn') }}" step="0.01" readonly>
                        </div>

                        {{-- Per KG --}}
                        <div class="col-md-3">
                            <label for="per_kg_price" class="form-label">Per KG Price</label>
                            <input type="number" id="per_kg_price" name="per_kg_price" class="form-control form-control-sm" value="{{ old('per_kg_price') }}" step="0.01" readonly>
                        </div>

                        {{-- Transaction Date --}}
                        <div class="col-md-2">
                            <label for="txn_date" class="form-label">දිනය <span class="text-danger">*</span></label>
                            <input type="date" id="txn_date" name="txn_date" class="form-control form-control-sm" value="{{ old('txn_date', date('Y-m-d')) }}">
                        </div>

                       
                    <div class="d-flex gap-2 align-items-end">
    <div class="flex-grow-1">
        <label for="total_grn" class="form-label">GRN Total</label>
        <input type="number" id="total_grn" name="total_grn" step="0.01" 
               value="{{ old('total_grn') }}"
               class="form-control @error('total_grn') is-invalid @enderror">
        @error('total_grn')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="flex-grow-1">
        <label for="per_kg_price" class="form-label">Per KG Price</label>
        <input type="number" id="per_kg_price" name="per_kg_price" step="0.01" 
               value="{{ old('per_kg_price') }}"
               class="form-control form-control-sm @error('per_kg_price') is-invalid @enderror">
        @error('per_kg_price')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="flex-grow-1 d-flex justify-content-center">
        <button type="submit" class="btn btn-primary btn-sm w-100">
            <i class="material-icons align-middle me-1">check_circle</i>GRN ADD
        </button>
    </div>

    <div class="flex-grow-1 d-flex justify-content-center">
        <a href="{{ route('grn.index') }}" class="btn btn-secondary btn-sm w-100">
            <i class="material-icons align-middle me-1">cancel</i>GRN REMOVE
        </a>
    </div>
</div>




                    </div>
                </form>

                {{-- GRN Table --}}
                <hr class="my-4">
                <div class="mb-3">
                    <label for="list_password" class="form-label">මුරපදය (View hidden columns)</label>
                    <input type="password" id="list_password" class="form-control form-control-sm">
                </div>
                <input type="text" id="searchInput" class="form-control form-control-sm mb-3" placeholder="Search by Code, Supplier, Item Code, or Name">

                @if($entries->isEmpty())
                    <div class="alert alert-info text-center" role="alert">
                        කිසිඳු GRN ඇතුළත් කර නොමැත.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover align-middle table-sm" id="entriesTable">
                            <thead class="table-light">
                                <tr>
                                    <th>කේතය</th>
                                    <th class="d-none">සැපයුම්කරුගේ කේතය</th>
                                    <th class="d-none">අයිතම කේතය</th>
                                    <th>අයිතම නාමය</th>
                                    <th>පැක්‌</th>
                                    <th>බර (kg)</th>
                                    <th>ගනුදෙනු දිනය</th>
                                    <th>GRN අංකය</th>
                                    <th class="total-grn-header">GRN මුළු එකතුව</th>
                                    <th class="per-kg-price-header">Per KG Price</th>
                                    <th>මෙහෙයුම්</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($entries as $entry)
                                    <tr class="grn-row" data-entry-id="{{ $entry->id }}">
                                        <td class="search-code">{{ $entry->code }}</td>
                                        <td class="search-supplier-code d-none">{{ $entry->supplier_code }}</td>
                                        <td class="search-item-code d-none">{{ $entry->item_code }}</td>
                                        <td class="search-item-name">{{ $entry->item_name }}</td>
                                        <td>{{ $entry->original_packs }}</td>
                                        <td>{{ $entry->original_weight }}</td>
                                        <td>{{ $entry->txn_date }}</td>
                                        <td>{{ $entry->grn_no }}</td>
                                        <td class="total-grn-column">{{ $entry->total_grn }}</td>
                                        <td class="per-kg-price-column">{{ $entry->PerKGPrice }}</td>
                                        <td>
                                            <a href="{{ route('grn.edit', $entry->id) }}" class="btn btn-sm btn-info me-1"><i class="material-icons">edit</i></a>
                                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteConfirmationModal" data-entry-id="{{ $entry->id }}"><i class="material-icons">delete</i></button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>

    {{-- Delete Confirmation Modal --}}
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteConfirmationModalLabel">Delete GRN Entry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this GRN entry? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteForm" method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Yes, Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Context menu --}}
    <ul class="custom-context-menu" id="contextMenu">
        <li id="hideOption">Hide</li>
        <li id="unhideOption">Don't Hide</li>
    </ul>

    {{-- ===================== Scripts ===================== --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Unlock Total & Per KG Price on password
            const passwordField = document.getElementById('password_total');
            const totalGrnField = document.getElementById('total_grn');
            const perKgField = document.getElementById('per_kg_price');

            passwordField.addEventListener('input', function () {
                const correctPassword = 'nethma123';
                if (this.value === correctPassword) {
                    totalGrnField.removeAttribute('readonly');
                    perKgField.removeAttribute('readonly');
                    passwordField.style.backgroundColor = '#d4edda';
                    passwordField.style.borderColor = '#28a745';
                } else {
                    totalGrnField.setAttribute('readonly', true);
                    perKgField.setAttribute('readonly', true);
                    passwordField.style.backgroundColor = '#f8d7da';
                    passwordField.style.borderColor = '#dc3545';
                }
                if (this.value === '') {
                    passwordField.style.backgroundColor = '';
                    passwordField.style.borderColor = '';
                }
            });

            // Supplier code hidden field
            const supplierNameInput = document.getElementById('supplier_name');
            const supplierCodeInput = document.getElementById('supplier_code_input');
            const suppliersDatalist = document.getElementById('suppliers_list');

            supplierNameInput.addEventListener('input', function() {
                let supplierCode = '';
                for (const option of suppliersDatalist.options) {
                    if (option.value === this.value) {
                        supplierCode = option.getAttribute('data-code');
                        break;
                    }
                }
                supplierCodeInput.value = supplierCode;
            });

            // GRN List: Context menu and hide/unhide
            const contextMenu = document.getElementById('contextMenu');
            let currentEntryId = null;

            document.querySelectorAll('.grn-row').forEach(row => {
                row.addEventListener('contextmenu', function (e) {
                    e.preventDefault();
                    currentEntryId = this.dataset.entryId;
                    contextMenu.style.top = `${e.pageY}px`;
                    contextMenu.style.left = `${e.pageX}px`;
                    contextMenu.style.display = 'block';
                });
            });
            document.addEventListener('click', () => contextMenu.style.display = 'none');

            const csrfToken = '{{ csrf_token() }}';
            document.getElementById('hideOption').addEventListener('click', function () {
                if (currentEntryId) {
                    fetch(`/grn/${currentEntryId}/hide`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json',
                        },
                    }).then(r => r.json()).then(() => {
                        alert("Entry marked as hidden in the database.");
                        contextMenu.style.display = 'none';
                    }).catch(() => alert("Failed to mark entry as hidden."));
                }
            });
            document.getElementById('unhideOption').addEventListener('click', function () {
                if (currentEntryId) {
                    fetch(`/grn/${currentEntryId}/unhide`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json',
                        },
                    }).then(r => r.json()).then(() => {
                        alert("Entry marked as visible in the database.");
                        contextMenu.style.display = 'none';
                    }).catch(() => alert("Failed to mark entry as visible."));
                }
            });

            // Delete modal dynamic URL
            const deleteModal = document.getElementById('deleteConfirmationModal');
            const deleteForm = document.getElementById('deleteForm');
            deleteModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const entryId = button.getAttribute('data-entry-id');
                deleteForm.action = `/grn/${entryId}`;
            });

            // Unlock hidden columns in GRN List
            const listPasswordField = document.getElementById('list_password');
            const totalGrnCells = document.querySelectorAll('.total-grn-column');
            const totalGrnHeader = document.querySelector('.total-grn-header');
            const perKgCells = document.querySelectorAll('.per-kg-price-column');
            const perKgHeader = document.querySelector('.per-kg-price-header');

            function toggleHiddenColumns(show) {
                const displayStyle = show ? 'table-cell' : 'none';
                totalGrnHeader.style.display = displayStyle;
                perKgHeader.style.display = displayStyle;
                totalGrnCells.forEach(cell => cell.style.display = displayStyle);
                perKgCells.forEach(cell => cell.style.display = displayStyle);
            }

            listPasswordField.addEventListener('input', function () {
                const isCorrect = this.value === 'nethma123';
                toggleHiddenColumns(isCorrect);
                if (isCorrect) {
                    listPasswordField.style.backgroundColor = '#d4edda';
                    listPasswordField.style.borderColor = '#28a745';
                } else {
                    listPasswordField.style.backgroundColor = '#f8d7da';
                    listPasswordField.style.borderColor = '#dc3545';
                }
                if (this.value === '') {
                    listPasswordField.style.backgroundColor = '';
                    listPasswordField.style.borderColor = '';
                }
            });

            // Search filter
            const searchInput = document.getElementById('searchInput');
            const rows = document.querySelectorAll('#entriesTable tbody tr');
            searchInput.addEventListener('keyup', function () {
                const filter = searchInput.value.toLowerCase();
                rows.forEach(row => {
                    const code = row.querySelector('.search-code').textContent.toLowerCase();
                    const supplierCode = row.querySelector('.search-supplier-code').textContent.toLowerCase();
                    const itemCode = row.querySelector('.search-item-code').textContent.toLowerCase();
                    const itemName = row.querySelector('.search-item-name').textContent.toLowerCase();
                    row.style.display = (code.includes(filter) || supplierCode.includes(filter) || itemCode.includes(filter) || itemName.includes(filter)) ? '' : 'none';
                });
            });
        });
    </script>
@endsection
