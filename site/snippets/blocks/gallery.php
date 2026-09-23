<?php

/**
 * 갤러리 블록 — Kirby 기본 스니펫을 덮어쓴 것
 *
 * 바꾼 것은 <li> 안의 이미지 한 줄뿐이다. 나머지는 업스트림 그대로 둔다.
 *
 * @upstream kirby/config/blocks/gallery/gallery.php e62e664072955961b9ecf260068e0b41d588b623
 *
 * 위 해시는 복사해 온 업스트림 파일의 sha1이다. Kirby를 올려 그 파일이 바뀌면 정적
 * 빌드가 멈춘다(scripts/build-static.php). 업스트림 diff를 이 파일에 옮긴 뒤 해시를
 * 새로 적는다.
 *
 * 근거: docs/adr/0018-responsive-images.md
 *
 * @var \Kirby\Cms\Block $block
 */
$caption = $block->caption();
$crop    = $block->crop()->isTrue();
$ratio   = $block->ratio()->or('auto');
$images  = $block->images()->toFiles();
?>
<figure<?= Html::attr(['data-ratio' => $ratio, 'data-crop' => $crop], null, ' ') ?>>
  <ul>
    <?php foreach ($images as $image): ?>
    <li>
      <?php
      // n장이 본문 열을 n등분하고, 좁은 화면에서는 한 열로 쌓인다(index.css의 figure ul)
      snippet('image', ['image' => $image, 'columns' => $images->count(), 'stack' => true]);
      ?>
    </li>
    <?php endforeach ?>
  </ul>
  <?php if ($caption->isNotEmpty()): ?>
  <figcaption>
    <?= $caption ?>
  </figcaption>
  <?php endif ?>
</figure>
