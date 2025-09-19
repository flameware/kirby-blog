<?php

use Uniform\Form;

return function ($kirby)
{
    $form = new Form([
        'email' => [
            'rules' => ['required', 'email'],
            'message' => '이메일 주소를 다시 한 번 확인해주세요.',
        ],
        'name' => [],
        'message' => [
            'rules' => ['required'],
            'message' => '메시지를 남겨주세요.',
        ],
    ]);

    if ($kirby->request()->is('POST')) {
        $form->emailAction([
            'to' => 'flameware@gmail.com',
            'from' => 'about@massivevoid.com',
        ])->done();
    }

    return compact('form');
};