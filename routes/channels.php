<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('farm.{id}', function ($user, $id) {
    return $user->farms()->where('farms.id', $id)->exists() || $user->isAdmin();
});
