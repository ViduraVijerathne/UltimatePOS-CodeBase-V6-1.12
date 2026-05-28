@extends('layouts.app')
@section('title', __( 'sale.drafts'))
@section('content')

<!-- Content Header (Page header) -->
<section class="content-header no-print">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('sale.drafts')
    </h1>
</section>

<!-- Main content -->
<section class="content no-print">
    @component('components.filters', ['title' => __('report.filters')])
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('sell_list_filter_location_id',  __('purchase.business_location') . ':') !!}

                {!! Form::select('sell_list_filter_location_id', $business_locations, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all') ]); !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('sell_list_filter_customer_id',  __('contact.customer') . ':') !!}
                {!! Form::select('sell_list_filter_customer_id', $customers, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('sell_list_filter_date_range', __('report.date_range') . ':') !!}
                {!! Form::text('sell_list_filter_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']); !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('created_by',  __('report.user') . ':') !!}
                {!! Form::select('created_by', $sales_representative, null, ['class' => 'form-control select2', 'style' => 'width:100%']); !!}
            </div>
        </div>
    @endcomponent
    @component('components.widget', ['class' => 'box-primary'])

@slot('tool')
    {{-- Solution 1: Enhanced Flexbox with additional classes --}}
    <div class="box-tools d-flex flex-row align-items-center justify-content-start" style="gap: 12px; display: flex !important; flex-direction: row !important;">
        <a class="tw-dw-btn tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full d-inline-flex align-items-center"
            href="{{ action([\App\Http\Controllers\SellController::class, 'create'], ['status' => 'draft']) }}"
            style="display: inline-flex !important; align-items: center; padding: 8px 16px; text-decoration: none; white-space: nowrap;">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="icon icon-tabler icons-tabler-outline icon-tabler-plus me-2"
                style="margin-right: 6px;">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M12 5l0 14" />
                <path d="M5 12l14 0" />
            </svg>
            @lang('lang_v1.add_draft')
        </a>
        
        <a class="tw-dw-btn tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full print-all-drafts d-inline-flex align-items-center"
            href="#"
            data-href="{{ route('sell.printAllDrafts') }}"
            style="display: inline-flex !important; align-items: center; padding: 8px 16px; text-decoration: none; white-space: nowrap;">
            <i class="fas fa-print me-2" aria-hidden="true" style="margin-right: 6px;"></i>
            @lang('lang_v1.print_all_draft')
        </a>
    </div>
@endslot

        <div class="table-responsive">
            <table class="table table-bordered table-striped ajax_view" id="sell_table">
                <thead>
                    <tr>
                        <th>@lang('messages.date')</th>
                        <th>@lang('purchase.ref_no')</th>
                        <th>@lang('sale.customer_name')</th>
                        <th>@lang('lang_v1.contact_no')</th>
                        <th>@lang('sale.location')</th>
                        <th>@lang('lang_v1.total_items')</th>
                        <th>@lang('lang_v1.added_by')</th>
                        <th>@lang('messages.action')</th>
                    </tr>
                </thead>
            </table>
        </div>
    @endcomponent
</section>
<!-- /.content -->
@stop
@section('javascript')
<script type="text/javascript">
$(document).ready( function(){
    $('#sell_list_filter_date_range').daterangepicker(
        dateRangeSettings,
        function (start, end) {
            $('#sell_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
            sell_table.ajax.reload();
        }
    );
    $('#sell_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
        $('#sell_list_filter_date_range').val('');
        sell_table.ajax.reload();
    });
    sell_table = $('#sell_table').DataTable({
        processing: true,
        serverSide: true,
        aaSorting: [[0, 'desc']],
        "ajax": {
            "url": '/sells/draft-dt?is_quotation=0',
            "data": function ( d ) {
                if($('#sell_list_filter_date_range').val()) {
                    var start = $('#sell_list_filter_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                    var end = $('#sell_list_filter_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                    d.start_date = start;
                    d.end_date = end;
                }

                if($('#sell_list_filter_location_id').length) {
                    d.location_id = $('#sell_list_filter_location_id').val();
                }
                d.customer_id = $('#sell_list_filter_customer_id').val();

                if($('#created_by').length) {
                    d.created_by = $('#created_by').val();
                }
            }
        },
        columnDefs: [ {
            "targets": 7,
            "orderable": false,
            "searchable": false
        } ],
        columns: [
            { data: 'transaction_date', name: 'transaction_date'  },
            { data: 'invoice_no', name: 'invoice_no'},
            { data: 'conatct_name', name: 'conatct_name'},
            { data: 'mobile', name: 'contacts.mobile'},
            { data: 'business_location', name: 'bl.name'},
            { data: 'total_quantity', name: 'total_items', "searchable": false},
            { data: 'added_by', name: 'added_by'},
            { data: 'action', name: 'action'}
        ],
        "fnDrawCallback": function (oSettings) {
            __currency_convert_recursively($('#purchase_table'));
        }
    });
    $(document).on('change', '#sell_list_filter_location_id, #sell_list_filter_customer_id, #created_by',  function() {
        sell_table.ajax.reload();
    });



    $(document).on('click', 'a.convert-to-proforma', function(e){
        e.preventDefault();
        swal({
            title: LANG.sure,
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then(confirm => {
            if (confirm) {
                var url = $(this).attr('href');
                $.ajax({
                    method: 'GET',
                    url: url,
                    dataType: 'json',
                    success: function(result) {
                        if (result.success == true) {
                            toastr.success(result.msg);
                            sell_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            }
        });
    });
    
      $(document).on('click', '.print-all-drafts', function(e) {
        e.preventDefault();
        
        var url = $(this).data('href');
        
        // Show loading indicator
        var $button = $(this);
        var originalText = $button.html();
        $button.html('<i class="fas fa-spinner fa-spin"></i> Printing...');
        $button.prop('disabled', true);
        
        $.ajax({
            method: 'GET',
            url: url,
            dataType: 'json',
            success: function(result) {
                if (result.success == 1) {
                    // Create a new window for printing
                    var printWindow = window.open('', '_blank', 'width=800,height=600,scrollbars=yes,resizable=yes');
                    
                    printWindow.document.open();
                    printWindow.document.write('<!DOCTYPE html>');
                    printWindow.document.write('<html><head><title>All Draft Items</title>');
                    printWindow.document.write('<style>');
                    printWindow.document.write('body { margin: 20px; font-family: Arial, sans-serif; }');
                    printWindow.document.write('table { width: 100%; border-collapse: collapse; }');
                    printWindow.document.write('th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }');
                    printWindow.document.write('th { background-color: #f2f2f2; font-weight: bold; }');
                    printWindow.document.write('@media print { body { margin: 0; } }');
                    printWindow.document.write('</style>');
                    printWindow.document.write('</head><body>');
                    printWindow.document.write(result.receipt);
                    printWindow.document.write('</body></html>');
                    printWindow.document.close();
                    
                    // Wait a moment for content to load, then print
                    setTimeout(function() {
                        printWindow.focus();
                        printWindow.print();
                        
                        // Optional: Close the window after printing
                        setTimeout(function() {
                            printWindow.close();
                        }, 1000);
                    }, 500);
                    
                } else {
                    // Show error message
                    alert('Error: ' + (result.msg || 'Unable to generate print document'));
                    console.log('Print error:', result);
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX Error:', error);
                console.log('Response:', xhr.responseText);
                alert('Error connecting to server. Please check console for details.');
            },
            complete: function() {
                // Restore button state
                $button.html(originalText);
                $button.prop('disabled', false);
            }
        });
    });
    
    
});
</script>
	
@endsection