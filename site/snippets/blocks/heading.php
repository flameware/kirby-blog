<?php

/**
 * 소제목 블록 — Kirby 기본 스니펫을 덮어쓴 것
 *
 * 필자가 고른 단계와 상관없이 언제나 h2다. 제목(h1)은 화면당 하나이고 본문에 다시
 * 나오지 않는다(ADR-0007).
 *
 * @upstream kirby/config/blocks/heading/heading.php d988a49fb6552804dc38238c62e9b21bc4f9d16c
 *
 * 위 해시는 이 파일이 따라가는 업스트림 파일의 sha1이다. Kirby를 올려 그 파일이
 * 바뀌면 정적 빌드가 멈춘다(scripts/build-static.php). 여기에 옮길 것이 있는지 본 뒤
 * 해시를 새로 적는다.
 *
 * @var \Kirby\Cms\Block $block
 */
?>
<h2 class="blog-heading">
    <?= $block->text() ?>
</h2>
