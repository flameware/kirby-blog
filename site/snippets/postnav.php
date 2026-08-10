<?php
/**
 * 이전/다음 페이지 내비게이션
 *
 * 이웃은 부모의 listed 자식 목록 순서(패널 num)를 따른다.
 * 목록에서 아래쪽 항목이 '이전', 위쪽 항목이 '다음'이다.
 * 블로그는 목록이 최신순이므로 '이전' = 더 오래된 글이 된다.
 * 목록에 없는(unlisted) 페이지에서는 순서상 위치가 정의되지 않으므로 표시하지 않는다.
 */
if ($page->isListed() !== true) {
    return;
}

$prev = $page->nextListed(); // 목록에서 아래쪽
$next = $page->prevListed(); // 목록에서 위쪽

if ($prev === null && $next === null) {
    return;
}
?>
<nav class="postnav" aria-label="글 이동">
    <?php if ($prev): ?>
    <a class="postnav-prev" href="<?= $prev->url() ?>" title="<?= $prev->title()->esc() ?>" aria-label="이전: <?= $prev->title()->esc() ?>">&lt; 이전</a>
    <?php endif ?>
    <?php if ($next): ?>
    <a class="postnav-next" href="<?= $next->url() ?>" title="<?= $next->title()->esc() ?>" aria-label="다음: <?= $next->title()->esc() ?>">다음 &gt;</a>
    <?php endif ?>
</nav>
