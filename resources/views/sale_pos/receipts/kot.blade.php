<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>KOT-{{$receipt_details->invoice_no}}</title>
    </head>
    <body>
        <div class="ticket">
            {{-- KOT Header --}}
            <div class="text-box">
                <p class="centered">
                    @if(!empty($receipt_details->logo))
                        <img style="max-height: 80px; width: auto; margin-bottom: 10px;" src="{{$receipt_details->logo}}" alt="Logo">
                        <br/>
                    @endif
                    <span class="kot-label">KOT</span>
                </p>
            </div>
            
            <div class="border-top"></div>
            
            {{-- Order Information --}}
            <div class="textbox-info">
                <p class="f-left"><strong>Order No:</strong></p>
                <p class="f-right">{{$receipt_details->invoice_no}}</p>
            </div>
            
            <div class="textbox-info">
                <p class="f-left"><strong>Date & Time:</strong></p>
                <p class="f-right">{{$receipt_details->invoice_date}}</p>
            </div>

            @if(!empty($receipt_details->types_of_service))
                <div class="textbox-info">
                    <p class="f-left"><strong>{!! $receipt_details->types_of_service_label !!}:</strong></p>
                    <p class="f-right">{{$receipt_details->types_of_service}}</p>
                </div>
            @endif
            
            @if(!empty($receipt_details->table))
                <div class="textbox-info">
                    <p class="f-left"><strong>Table:</strong></p>
                    <p class="f-right"><span class="table-number">{{$receipt_details->table}}</span></p>
                </div>
            @endif
            
            @if(!empty($receipt_details->service_staff))
                <div class="textbox-info">
                    <p class="f-left"><strong>Waiter:</strong></p>
                    <p class="f-right">{{$receipt_details->service_staff}}</p>
                </div>
            @endif
            
            @if(!empty($receipt_details->customer_name))
                <div class="textbox-info">
                    <p class="f-left"><strong>Customer:</strong></p>
                    <p class="f-right">{{$receipt_details->customer_name}}</p>
                </div>
            @endif
            
            <div class="border-bottom mt-10 mb-10"></div>
            
            {{-- Items Table --}}
            <table class="width-100 kot-table">
                <thead>
                    <tr class="border-bottom">
                        <th class="text-left" style="width: 10%;">#</th>
                        <th class="text-left" style="width: 55%;">Item</th>
                        <th class="text-right" style="width: 35%;">Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($receipt_details->lines as $line)
                        <tr class="item-row">
                            <td class="item-number">#{{$loop->iteration}}</td>
                            <td class="item-name">
                                <div class="item-main-name">{{$line['name']}}</div>
                                @if(!empty($line['variation']))
                                    <div class="item-variation">
                                        {{$line['product_variation']}} {{$line['variation']}}
                                    </div>
                                @endif
                                @if(!empty($line['sell_line_note']))
                                    <div class="item-note">
                                        <strong>Note:</strong> {!! $line['sell_line_note'] !!}
                                    </div>
                                @endif
                            </td>
                            <td class="item-qty text-right">x {{$line['quantity']}}</td>
                        </tr>
                        
                        {{-- Modifiers/Add-ons --}}
                        @if(!empty($line['modifiers']))
                            @foreach($line['modifiers'] as $modifier)
                                <tr class="modifier-row">
                                    <td></td>
                                    <td>
                                        <div class="modifier-name">
                                            + {{$modifier['name']}} {{$modifier['variation']}}
                                        </div>
                                        @if(!empty($modifier['sell_line_note']))
                                            <div class="modifier-note">
                                                ({!! $modifier['sell_line_note'] !!})
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-right">x {{$modifier['quantity']}}</td>
                                </tr>
                            @endforeach
                        @endif
                        
                        {{-- Spacing between items --}}
                        @if(!$loop->last)
                            <tr class="spacer-row">
                                <td colspan="3">&nbsp;</td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
            
            <div class="border-bottom mt-10 mb-10"></div>
            
            {{-- Special Instructions --}}
            @if(!empty($receipt_details->additional_notes))
                <div class="special-instructions">
                    <p class="instructions-label"><strong>SPECIAL INSTRUCTIONS:</strong></p>
                    <p class="instructions-text">{!! nl2br($receipt_details->additional_notes) !!}</p>
                </div>
                <div class="border-bottom mt-10 mb-10"></div>
            @endif
            
            {{-- Summary --}}
            <div class="summary-section">
                <p class="centered summary-text">
                    <strong>Total Items: {{$receipt_details->total_quantity ?? count($receipt_details->lines)}}</strong>
                </p>
            </div>
            
            <div class="border-bottom mt-10 mb-10"></div>
            
            {{-- Footer --}}
            <p class="centered kitchen-copy">
                *** KITCHEN COPY ***
            </p>
            
            <p class="centered timestamp">
                Printed: {{\Carbon\Carbon::now()->format('d-m-Y h:i A')}}
            </p>
        </div>
    </body>
</html>

<style type="text/css">
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    color: #000000;
    font-family: 'Times New Roman', Times, serif;
    font-size: 12px;
    line-height: 1.4;
}

.ticket {
    width: 100%;
    max-width: 100%;
    padding: 10px;
}

.text-box {
    width: 100%;
    margin-bottom: 10px;
}

.centered {
    text-align: center;
}

.headings {
    font-size: 18px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.kot-label {
    font-size: 20px;
    font-weight: 900;
    letter-spacing: 3px;
    display: inline-block;
    padding: 5px 20px;
    border: 2px solid #000;
    margin-top: 5px;
}

.border-top {
    border-top: 2px solid #000;
    margin: 10px 0;
}

.border-bottom {
    border-bottom: 2px solid #000;
    margin: 10px 0;
}

.textbox-info {
    display: flex;
    justify-content: space-between;
    padding: 3px 0;
    clear: both;
}

.textbox-info p {
    margin: 0;
}

.f-left {
    text-align: left;
}

.f-right {
    text-align: right;
    font-weight: bold;
}

.table-number {
    font-size: 18px;
    font-weight: 900;
    padding: 2px 10px;
    background: #000;
    color: #fff;
    display: inline-block;
}

.mt-10 {
    margin-top: 10px;
}

.mb-10 {
    margin-bottom: 10px;
}

.width-100 {
    width: 100%;
}

.text-left {
    text-align: left;
}

.text-right {
    text-align: right;
}

/* KOT Table Styles */
.kot-table {
    border-collapse: collapse;
    margin: 10px 0;
}

.kot-table thead th {
    padding: 8px 5px;
    font-size: 13px;
    font-weight: bold;
    border-bottom: 2px solid #000;
}

.kot-table tbody tr.item-row {
    border-bottom: 1px solid #ddd;
}

.kot-table tbody td {
    padding: 8px 5px;
    vertical-align: top;
}

.item-number {
    font-size: 14px;
    font-weight: bold;
}

.item-main-name {
    font-size: 15px;
    font-weight: bold;
    margin-bottom: 3px;
}

.item-variation {
    font-size: 12px;
    color: #333;
    margin-bottom: 3px;
}

.item-note {
    font-size: 11px;
    font-style: italic;
    color: #555;
    margin-top: 5px;
    padding: 5px;
    background: #f5f5f5;
    border-left: 3px solid #000;
}

.item-qty {
    font-size: 18px;
    font-weight: 900;
}

.modifier-row td {
    padding: 5px 5px 5px 15px;
}

.modifier-name {
    font-size: 13px;
    color: #333;
}

.modifier-note {
    font-size: 11px;
    font-style: italic;
    color: #666;
    margin-top: 2px;
}

.spacer-row td {
    padding: 5px 0;
}

/* Special Instructions */
.special-instructions {
    padding: 10px;
    background: #f9f9f9;
    border: 2px dashed #000;
    margin: 10px 0;
}

.instructions-label {
    font-size: 13px;
    font-weight: bold;
    margin-bottom: 5px;
}

.instructions-text {
    font-size: 12px;
    line-height: 1.6;
}

/* Summary */
.summary-section {
    padding: 10px 0;
}

.summary-text {
    font-size: 16px;
}

/* Footer */
.kitchen-copy {
    font-size: 14px;
    font-weight: bold;
    letter-spacing: 2px;
}

.timestamp {
    font-size: 10px;
    color: #666;
    margin-top: 5px;
}

/* Print Styles */
@media print {
    body {
        font-size: 12px;
    }
    
    .ticket {
        padding: 5px;
    }
    
    .headings {
        font-size: 20px;
    }
    
    .kot-label {
        font-size: 22px;
    }
    
    .item-main-name {
        font-size: 16px;
    }
    
    .item-qty {
        font-size: 20px;
    }
    
    .table-number {
        font-size: 20px;
    }
    
    /* Ensure proper page breaks */
    .kot-table {
        page-break-inside: avoid;
    }
    
    .item-row {
        page-break-inside: avoid;
    }
}
</style>
