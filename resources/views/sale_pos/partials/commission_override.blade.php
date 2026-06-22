@php
    $commissionOverrideType = !empty($transaction->commission_type) ? $transaction->commission_type : 'percentage';
    $commissionOverrideAmount = isset($transaction) && !is_null($transaction->commission_amount) ? @num_format($transaction->commission_amount) : null;
    $commissionOverrideClass = !empty($commission_override_class) ? $commission_override_class : 'col-md-4 col-sm-6';
@endphp

<div class="{{ $commissionOverrideClass }} commission-override-wrapper @if(empty($transaction->commission_agent)) hide @endif" id="commission_override_box">
    <div class="form-group">
        {!! Form::label('commission_amount', __('lang_v1.commission_override') . ':') !!}
        <div class="input-group">
            <span class="input-group-addon">
                <i class="fa fa-percent"></i>
            </span>
            {!! Form::select('commission_type',
                ['percentage' => __('lang_v1.commission_percentage'), 'fixed' => __('lang_v1.commission_fixed')],
                $commissionOverrideType,
                ['class' => 'form-control input-sm', 'id' => 'commission_type']
            ); !!}
            {!! Form::text('commission_amount', $commissionOverrideAmount, ['class' => 'form-control input-sm input_number', 'id' => 'commission_amount', 'placeholder' => __('lang_v1.commission_amount')]); !!}
        </div>
    </div>
</div>
