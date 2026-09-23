<?php

/**
 * 이미지 블록 — Kirby 기본 스니펫을 덮어쓴 것
 *
 * 바꾼 것은 <img> 한 줄뿐이다. 나머지는 업스트림 그대로 둔다.
 *
 * @upstream kirby/config/blocks/image/image.php 39553c5c88ebc0adb7790b98748c1c4a329646dd
 *
 * 위 해시는 복사해 온 업스트림 파일의 sha1이다. Kirby를 올려 그 파일이 바뀌면 정적
 * 빌드가 멈춘다(scripts/build-static.php). 업스트림 diff를 이 파일에 옮긴 뒤 해시를
 * 새로 적는다.
 *
 * 근거: docs/adr/0018-responsive-images.md
 *
 * @var \Kirby\Cms\Block $block
 */
$alt     = $block->alt();
$caption = $block->caption();
$crop    = $block->crop()->isTrue();
$link    = $block->link();
$ratio   = $block->ratio()->or('auto');
$src     = null;
$image   = null;

if ($block->location() == 'web') {
	$src = $block->src()->esc();
} elseif ($image = $block->image()->toFile()) {
	$alt = $alt->or($image->alt());
	$src = $image->url();
}

// 바깥 주소의 이미지는 줄일 수 없으므로 업스트림 그대로 그린다
$img = $image
	? snippet('image', ['image' => $image, 'alt' => $alt], true)
	: '<img src="' . $src . '" alt="' . $alt->esc() . '">';

?>
<?php if ($src): ?>
<figure<?= Html::attr(['data-ratio' => $ratio, 'data-crop' => $crop], null, ' ') ?>>
  <?php if ($link->isNotEmpty()): ?>
  <a href="<?= Str::esc($link->toUrl()) ?>">
    <?= $img ?>
  </a>
  <?php else: ?>
  <?= $img ?>
  <?php endif ?>

  <?php if ($caption->isNotEmpty()): ?>
  <figcaption>
    <?= $caption ?>
  </figcaption>
  <?php endif ?>
</figure>
<?php endif ?>
