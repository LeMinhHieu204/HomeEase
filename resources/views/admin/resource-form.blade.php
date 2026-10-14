@extends('layouts.app')

@section('content')
<section class="admin-shell">
    <div class="container">
        <div class="admin-header"><div><span class="eyebrow">HomeEase admin</span><h1>{{ $heading }}.</h1><p>Every value is validated on the server before it is saved.</p></div><a class="text-link" href="{{ $back }}">Back to list <span>↗</span></a></div>
        <form method="POST" action="{{ $action }}" class="admin-form">
            @csrf
            @if($method !== 'POST') @method($method) @endif
            <div class="admin-form__main"><div class="admin-panel">
                @foreach($fields as $field)
                    <label>{{ $field['label'] }}
                        @if($field['type'] === 'textarea')
                            <textarea name="{{ $field['name'] }}" rows="6">{{ old($field['name'], $field['value'] ?? '') }}</textarea>
                        @elseif($field['type'] === 'select')
                            <select name="{{ $field['name'] }}">@foreach($field['options'] ?? [] as $value => $label)<option value="{{ $value }}" @selected((string) old($field['name'], $field['value'] ?? '') === (string) $value)>{{ $label }}</option>@endforeach</select>
                        @elseif($field['type'] === 'checkbox')
                            <span class="check-row"><input type="checkbox" name="{{ $field['name'] }}" value="1" @checked(old($field['name'], $field['value'] ?? false))> Enabled</span>
                        @else
                            <input type="{{ $field['type'] }}" name="{{ $field['name'] }}" value="{{ old($field['name'], $field['value'] ?? '') }}" @if(!empty($field['step'])) step="{{ $field['step'] }}" @endif>
                        @endif
                        @error($field['name'])<small class="form-error">{{ $message }}</small>@enderror
                    </label>
                @endforeach
            </div><div class="admin-panel"><h2>Save changes</h2><p class="form-note">Changes are written to MySQL and recorded in the audit log.</p><button class="button button--dark" type="submit">Save record <span>↗</span></button></div></div>
        </form>
    </div>
</section>
@endsection
