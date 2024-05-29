<form class="form-horizontal margin-top margin-bottom" method="POST" action="" id="telegram_notification_form">
    {{ csrf_field() }}

    <div class="form-group{{ $errors->has('settings.telegram_notification.active') ? ' has-error' : '' }} margin-bottom-10">
        <label for="telegram_notification.active" class="col-sm-2 control-label">{{ __('Активно') }}</label>

        <div class="col-sm-6">
            <input id="telegram_notification.active" type="checkbox" class=""
                   name="settings[telegram_notification.active]"
                   @if (old('settings[telegram_notification.active]', $settings['telegram_notification.active']) == 'on') checked="checked" @endif
            />
        </div>
    </div>

    <div class="form-group{{ $errors->has('settings.telegram_notification.token') ? ' has-error' : '' }} margin-bottom-10">
        <label for="telegram_notification.token" class="col-sm-2 control-label">{{ __('Bot Token') }}</label>

        <div class="col-sm-6">
            <input id="telegram_notification.token" type="text" class="form-control input-sized-lg"
                   name="settings[telegram_notification.token]" value="{{ old('settings.telegram_notification.token', $settings['telegram_notification.token']) }}">
            @include('partials/field_error', ['field'=>'settings.telegram_notification.token'])
        </div>
    </div>
    <div class="form-group{{ $errors->has('settings.telegram_notification.chat_id') ? ' has-error' : '' }} margin-bottom-10">
        <label for="telegram_notification.chat_id" class="col-sm-2 control-label">{{ __('Client Secret') }}</label>

        <div class="col-sm-6">
            <input id="telegram_notification.chat_id" type="text" class="form-control input-sized-lg"
                   name="settings[telegram_notification.chat_id]" value="{{ old('settings.telegram_notification.chat_id', $settings['telegram_notification.chat_id']) }}">
        </div>
    </div>

    <div class="form-group margin-top margin-bottom">
        <div class="col-sm-6 col-sm-offset-2">
            <button type="submit" class="btn btn-primary">
                {{ __('Save') }}
            </button>
        </div>
    </div>
</form>