<?php snippet('header') ?>


<main class="main">
        <?php /* 본문이 인사말로 시작하는 화면이라 제목을 그리지 않는다. 문서에는 있어야 하므로 숨긴다.
                 근거: docs/adr/0011-page-title-outline.md */ ?>
        <h1 class="visually-hidden"><?= $page->title()->esc() ?></h1>

        <?= $page->blocks()->toBlocks() ?>

<!--
<hr>
<p class="form-intro">연락이 필요하시면 아래에 이메일주소와 이름, 메시지를 남겨주세요.</p>

<form action="<?php echo $page->url() ?>" method="POST">
    <label>Email 주소</label>
    <input<?php if ($form->error('email')): ?> class="error"<?php endif; ?> name="email" type="email" value="<?php echo $form->old('email') ?>">

    <label>이름</label>
    <input<?php if ($form->error('name')): ?> class="error"<?php endif; ?> name="name" type="text" value="<?php echo $form->old('name') ?>">

    <label>메시지</label>
    <textarea<?php if ($form->error('message')): ?> class="error"<?php endif; ?> name="message"><?php echo $form->old('message') ?></textarea>

    <?php echo csrf_field() ?>
    <?php echo honeypot_field() ?>
    <input type="submit" value="보내기" class="submitbutton">
</form>
<?php if ($form->success()): ?>
    메시지를 보내주셔서 감사합니다. 확인후에 연락드릴께요!
<?php else: ?>
    <?php snippet('uniform/errors', ['form' => $form]) ?>
<?php endif; ?>
-->

    </main>


<?php snippet('footer') ?>