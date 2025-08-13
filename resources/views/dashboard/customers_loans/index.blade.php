@extends('layouts.app')

@section('content')
    <style>
        body {
            background-color: #99ff99 !important;
        }

        .custom-card {
            background-color: #004d00 !important;
            color: #fff;
            padding: 25px;
            border-radius: 10px;
        }

        .form-control,
        .form-select {
            padding: 0.15rem 0.4rem !important;
            font-size: 0.75rem !important;
            border: 1px solid black !important;
            color: black !important;
            font-weight: bold !important;
            background-color: white !important;
        }

        .table td,
        .table th {
            padding: 0.3rem;
            font-size: 0.875rem;
        }

        label {
            font-weight: 500;
            margin-bottom: 0.2rem;
            color: #000;
        }

        .table th {
            background-color: #006600;
            color: white;
        }

        h3,
        h4 {
            color: #ffffff;
        }

        .bg-custom-dark {
            background-color: #004d00 !important;
            color: #fff;
        }

        .text-form-label {
            color: #fff !important;
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 0.2rem;
        }

        .form-control-sm,
        .form-select-sm {
            padding: 0.15rem 0.4rem !important;
            font-size: 0.75rem !important;
            border: 1px solid black !important;
            color: black !important;
            font-weight: bold !important;
            background-color: white !important;
        }

        .bg-custom-dark strong {
            color: #fff !important;
        }
    </style>

    <div class="container my-4">
        <div class="custom-card">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            {{-- Loan Entry Form --}}
            <form method="POST" action="{{ route('customers-loans.store') }}" id="loanForm"
                class="p-3 border border-2 border-dark rounded bg-custom-dark">
                @csrf
                {{-- Laravel method spoofing --}}
                <input type="hidden" name="_method" id="methodField" value="POST">
                <input type="hidden" name="loan_id" id="loan_id">

                <div class="row gy-2">
                    <div class="col-md-8">
                        <label class="me-3">
                            <input type="radio" name="loan_type" value="old" checked>
                            වෙළෙන්දාගේ ලාද පරණ නය
                        </label>

                        <label class="me-3">
                            <input type="radio" name="loan_type" value="today">
                            වෙළෙන්දාගේ අද දින නය ගැනීම
                        </label>

                        <label class="me-3">
                            <input type="radio" name="loan_type" value="ingoing">
                            වෙනත් ලාභීම/ආදායම්
                        </label>

                        <label>
                            <input type="radio" name="loan_type" value="outgoing">
                            වි‍යදම්
                        </label>
                    </div>

                    <div class="col-md-4" id="settlingWaySection">
                        <label class="text-form-label"><strong>Settling Way:</strong></label><br>
                        <label class="me-3">
                            <input type="radio" name="settling_way" value="cash" checked>
                            Cash
                        </label>
                        <label>
                            <input type="radio" name="settling_way" value="cheque">
                            Cheque
                        </label>
                    </div>

                    <div class="col-md-4" id="customer_section">
                        <label for="customer_id" class="text-form-label">ගෙණුම්කරු</label>
                        <select class="form-select form-select-sm" id="customer_id" name="customer_id" required>
                            <option value="">-- Select Customer --</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" data-credit-limit="{{ $customer->credit_limit }}">{{ $customer->short_name }} - {{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3" id="bill_no_section">
                        <label for="bill_no" class="text-form-label">Bill No</label>
                        <input type="text" class="form-control form-control-sm" name="bill_no">
                    </div>

                    <div id="loan-details-row" class="row gx-2">
                        <div class="col-md-2" id="amount_section">
                            <label for="amount" class="text-form-label">මුදල</label>
                            <input type="number" step="0.01" class="form-control form-control-sm" name="amount" required>
                            <span id="creditLimitMessage" class="text-danger" style="font-weight: bold; font-size: 0.8rem;"></span>
                        </div>
                        <div class="col-md-5" id="description_section">
                            <label for="description" class="text-form-label">විස්තරය</label>
                            <input type="text" class="form-control form-control-sm" name="description" id="description"
                                required>
                            <span id="totalAmountDisplay" class="text-white-50"
                                style="font-weight: bold; font-size: 0.9rem;"></span>
                        </div>
                    </div>

                    <div id="chequeFields" class="col-md-5 ms-auto d-none">
                        <div class="border rounded p-2 bg-light" style="border-color: #006600 !important;">
                            <h6 class="text-success fw-bold mb-2" style="border-bottom: 1px solid #006600;">Cheque Details
                            </h6>
                            <div class="row g-2">
                                <div class="col-4">
                                    <label for="cheque_date" class="form-label mb-1">Cheque Date</label>
                                    <input type="date" class="form-control form-control-sm" name="cheque_date"
                                        value="{{ date('Y-m-d') }}" disabled>
                                </div>
                                <div class="col-4">
                                    <label for="cheque_no" class="form-label mb-1">Cheque No</label>
                                    <input type="text" class="form-control form-control-sm" name="cheque_no" disabled>
                                </div>
                                <div class="col-4">
                                    <label for="bank" class="form-label mb-1">Bank</label>
                                    <input type="text" class="form-control form-control-sm" name="bank" id="bank" disabled>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 mt-3">
                        <button type="submit" class="btn" id="submitButton">Add Loan</button>
                        <button type="button" class="btn btn-secondary" id="cancelEditButton"
                            style="display:none;">Cancel</button>
                    </div>
                </div>
            </form>

            <h4>Loan Records</h4>
            <div class="table-responsive">
                <table class="table table-bordered table-sm mt-2 bg-white text-dark">
                    <thead>
                        <tr>
                            <th>විස්තරය</th>
                            <th>මුදල</th>
                            <th>විලා</th>
                            <th>Loan Type</th>
                            <th>Bill No</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($loans as $loan)
                            <tr class="loan-row" data-loan='@json($loan)'>
                                <td>{{ $loan->description }}</td>
                                <td>{{ number_format($loan->amount, 2) }}</td>
                                <td>{{ $loan->customer_short_name }}</td>
                                <td>{{ ucfirst($loan->loan_type) }}</td>
                                <td>{{ $loan->bill_no ?? '-' }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-warning edit-loan-btn">Edit</button>
                                    <form action="{{ route('customers-loans.destroy', $loan->id) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE') <button type="submit" onclick="return confirm('Are you sure?')"
                                            class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No loan records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-3 text-end">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#reportLoanModal" class="btn btn-dark">
                        ණය වාර්තාව
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Include Select2 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    {{-- Include jQuery and Select2 JS --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        function updateDescription() {
            const loanType = document.querySelector('input[name="loan_type"]:checked').value;
            const settlingWay = document.querySelector('input[name="settling_way"]:checked').value;
            const descriptionField = document.getElementById('description');
            const bankField = document.getElementById('bank');
            const customerId = $('#customer_id').val();
            const totalAmountDisplay = $('#totalAmountDisplay');

            totalAmountDisplay.text('');
            descriptionField.value = "";

            if (loanType === 'old') {
                descriptionField.value = "වෙළෙන්දාගේ ලාද පරණ නය";
                if (settlingWay === 'cheque') {
                    const bankName = bankField.value.trim();
                    descriptionField.value = `Cheque payment from ${bankName || 'bank'}`;
                }
            } else if (loanType === 'today') {
                descriptionField.value = "වෙළෙන්දාගේ අද දින නය ගැනීම";
            } else if (loanType === 'ingoing') {
                descriptionField.value = "වෙනත් ලාභීම/ආදායම්";
            } else if (loanType === 'outgoing') {
                descriptionField.value = "වි‍යදම්";
            }

            if (customerId && (loanType === 'today' || loanType === 'old')) {
                $.ajax({
                    url: `/customers/${customerId}/loans-total`,
                    method: 'GET',
                    success: function (response) {
                        const formattedAmount = parseFloat(response.total_amount).toLocaleString(undefined, {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                        totalAmountDisplay.text(`(Total Loans: ${formattedAmount})`);
                    },
                    error: function () {
                        totalAmountDisplay.text('(Could not fetch total loans)');
                    }
                });
            }
        }

        function toggleLoanTypeDependentFields() {
            const loanType = $('input[name="loan_type"]:checked').val();
            const settlingWay = $('input[name="settling_way"]:checked').val();

            const isIngoingOrOutgoing = (loanType === 'ingoing' || loanType === 'outgoing');

            if (isIngoingOrOutgoing) {
                $('#settlingWaySection').addClass('d-none');
                $('#settlingWaySection input').prop('disabled', true);
                $('#customer_section').addClass('d-none');
                $('#customer_id').prop('disabled', true);
                $('#bill_no_section').addClass('d-none');
                $('input[name="bill_no"]').prop('disabled', true);
                $('#chequeFields').addClass('d-none');
                $('#chequeFields input').prop('disabled', true);

                $('#amount_section').removeClass('col-md-2').addClass('col-md-3');
                $('#description_section').removeClass('col-md-5').addClass('col-md-9');

                $('#customer_id').val(null).trigger('change');
            }
            else {
                $('#customer_section').removeClass('d-none');
                $('#customer_id').prop('disabled', false);
                $('#bill_no_section').removeClass('d-none');
                $('input[name="bill_no"]').prop('disabled', false);

                $('#amount_section').removeClass('col-md-3').addClass('col-md-2');
                $('#description_section').removeClass('col-md-9').addClass('col-md-5');

                if (loanType === 'today') {
                    $('#settlingWaySection').addClass('d-none');
                    $('#settlingWaySection input').prop('disabled', true);
                    $('#chequeFields').addClass('d-none');
                    $('#chequeFields input').prop('disabled', true);
                    $('input[name="bill_no"]').prop('disabled', false);
                } else if (loanType === 'old') {
                    $('#settlingWaySection').removeClass('d-none');
                    $('#settlingWaySection input').prop('disabled', false);

                    if (settlingWay === 'cheque') {
                        $('#chequeFields').removeClass('d-none');
                        $('#chequeFields input').prop('disabled', false);
                        $('input[name="bill_no"]').prop('disabled', true);
                    } else {
                        $('#chequeFields').addClass('d-none');
                        $('#chequeFields input').prop('disabled', true);
                        $('input[name="bill_no"]').prop('disabled', false);
                    }
                }
            }
            updateDescription();
        }

        // Function to reset the form to 'add' state
        function resetForm() {
            $('#loanForm')[0].reset();
            $('#loanForm').attr('action', "{{ route('customers-loans.store') }}");
            $('#methodField').val('POST');
            $('input[name="loan_type"][value="old"]').prop('checked', true);
            $('input[name="settling_way"][value="cash"]').prop('checked', true);
            $('#customer_id').val(null).trigger('change');
            toggleLoanTypeDependentFields();
            updateDescription();
            
            // Set button for 'Add' state
            $('#submitButton').text('Add Loan').removeClass('btn-success').addClass('btn-light text-dark');
            $('#cancelEditButton').hide();
            $('#creditLimitMessage').text('');
        }

        $(document).ready(function () {
            $('#customer_id, #filter_customer').select2({
                placeholder: "-- Select Customer --",
                allowClear: true,
                width: '100%'
            });

            $('#customer_id').on('select2:open', function () {
                setTimeout(function () {
                    $('.select2-container--open .select2-search__field').focus();
                }, 50);
            });

            $('input[name="settling_way"]').on('change', toggleLoanTypeDependentFields);
            $('input[name="loan_type"]').on('change', toggleLoanTypeDependentFields);
            $('#bank').on('input', updateDescription);
            $('#customer_id').on('change', updateDescription);
            
            // Initialize the form to 'add' mode on page load
            resetForm();

            // Edit button click handler
            $('.edit-loan-btn').on('click', function () {
                const loan = $(this).closest('tr').data('loan');

                // Change form action URL to update route
                $('#loanForm').attr('action', `/customers-loans/${loan.id}`);
                $('#methodField').val('PUT');

                $('#loan_id').val(loan.id);

                if (loan.customer_id) {
                    $('#customer_id').val(loan.customer_id).trigger('change');
                } else {
                    $('#customer_id').val(null).trigger('change');
                }

                $('input[name="loan_type"][value="' + loan.loan_type + '"]').prop('checked', true);
                $('input[name="amount"]').val(loan.amount);
                $('input[name="description"]').val(loan.description);

                if (loan.loan_type === 'today') {
                    $('input[name="settling_way"]').prop('checked', false);
                    $('input[name="bill_no"]').val(loan.bill_no ?? '');
                } else {
                    $('input[name="settling_way"][value="' + (loan.settling_way ?? 'cash') + '"]').prop('checked', true);
                    if (loan.settling_way === 'cheque') {
                        $('input[name="cheque_date"]').val(loan.cheque_date ?? '');
                        $('input[name="cheque_no"]').val(loan.cheque_no ?? '');
                        $('input[name="bank"]').val(loan.bank ?? '');
                        $('input[name="bill_no"]').val('');
                    } else {
                        $('input[name="bill_no"]').val(loan.bill_no ?? '');
                    }
                }

                toggleLoanTypeDependentFields();
                updateDescription();

                // Set button for 'Update' state
                $('#submitButton').text('Update Loan').removeClass('btn-light text-dark').addClass('btn-success');
                $('#cancelEditButton').show();
            });

            $('#cancelEditButton').on('click', function () {
                resetForm();
            });
            
            $('#customer_id').on('select2:close', function () {
                $('input[name="bill_no"]').focus();
            });
            $('input[name="bill_no"]').on('keypress', function (e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $('input[name="amount"]').focus();
                }
            });
            $('input[name="amount"]').on('keypress', function (e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $('input[name="description"]').focus();
                }
            });
            $('input[name="description"]').on('keypress', function (e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $('#submitButton').click();
                }
            });
            
            function checkCreditLimit() {
                const loanType = $('input[name="loan_type"]:checked').val();
                const customerId = $('#customer_id').val();
                const amount = parseFloat($('input[name="amount"]').val());
                const creditLimitMessage = $('#creditLimitMessage');
                const submitButtons = $('#submitButton');
                const selectedCustomerOption = $('#customer_id option:selected');
                const creditLimit = parseFloat(selectedCustomerOption.data('credit-limit'));

                creditLimitMessage.text('');
                submitButtons.prop('disabled', false);

                if ((loanType === 'today' || loanType === 'old') && customerId && amount > 0) {
                    if (!isNaN(creditLimit) && amount > creditLimit) {
                        creditLimitMessage.text('Amount exceeds credit limit!');
                        submitButtons.prop('disabled', true);
                    }
                }
            }

            $('input[name="amount"]').on('input', checkCreditLimit);
            $('#customer_id').on('change', checkCreditLimit);
            $('input[name="loan_type"]').on('change', checkCreditLimit);
        });
    </script>
@endsection