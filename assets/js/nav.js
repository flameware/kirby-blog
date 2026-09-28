/**
 * 내비게이션이 화면 위에 붙었는지를 data-stuck으로 알린다.
 *
 * 붙었을 때만 아래에 선을 긋기 위해서다. 흐름 안에 있을 때 선이 있으면 내비게이션이
 * 첫 화면에서 전폭 바로 읽히고, 붙은 뒤에 선이 없으면 본문이 흰 띠 밑으로 경계 없이
 * 잘려 들어간다. CSS만으로는 sticky가 붙었는지 알 수 없다.
 *
 * 스크롤 이벤트가 아니라 IntersectionObserver를 쓴다. 관찰 영역의 위를 1px 깎아 두면,
 * 내비게이션이 top: 0에 붙는 순간 윗줄 1px이 영역 밖으로 나가 온전히 보이지 않게 된다.
 * 그 전에는 위 여백(모바일 8px, 데스크탑 5.6vw − 12px)만큼 떨어져 있어 온전히 보인다.
 *
 * 스크립트가 없거나 실패하면 선이 안 보일 뿐이다. 붙는 것 자체는 CSS가 한다.
 * 근거: docs/adr/0019-navigation-column-header.md
 */
(() => {
    const nav = document.querySelector(".mainnav");
    if (!nav || !("IntersectionObserver" in window)) return;

    new IntersectionObserver(
        ([entry]) => nav.toggleAttribute("data-stuck", entry.intersectionRatio < 1),
        { rootMargin: "-1px 0px 0px 0px", threshold: 1 },
    ).observe(nav);
})();
