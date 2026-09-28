// PROTOTYPE — 버린다. prototype/nav-variants 브랜치에만 있다.
// 떠 있는 전환 패널. 상태는 localStorage에 두어 화면을 옮겨도 유지된다.
// 깜빡임을 막는 초기 적용은 header.php의 인라인 스크립트가 먼저 한다.
(() => {
  const VARIANTS = [
    ["0", "현재 — island"],
    ["a", "A — 열 정렬 텍스트 헤더"],
    ["b", "B — 전체폭 바"],
    ["c", "C — 마스트헤드"],
  ];
  const root = document.documentElement;
  const get = (k, d) => { try { return localStorage.getItem(k) ?? d; } catch { return d; } };
  const set = (k, v) => { try { v === null ? localStorage.removeItem(k) : localStorage.setItem(k, v); } catch {} };

  let idx = Math.max(0, VARIANTS.findIndex(([k]) => k === get("proto-nav", "a")));
  let sticky = get("proto-sticky", "") === "1";
  let pad = get("proto-sticky-pad", "0");
  let line = get("proto-sticky-line", "none");

  const bar = document.createElement("div");
  bar.className = "proto-bar";
  bar.innerHTML = `
    <button data-act="prev" aria-label="이전 시안">←</button>
    ${VARIANTS.map(([k]) => `<button data-key="${k}">${k === "0" ? "현재" : k.toUpperCase()}</button>`).join("")}
    <button data-act="next" aria-label="다음 시안">→</button>
    <span class="proto-label"></span>
    <span class="proto-sep"></span>
    <label><input type="checkbox" data-act="sticky"> sticky</label>
    <select data-act="pad" title="sticky 세로 여백"><option value="0">여백 0</option><option value="8">여백 8</option><option value="12">여백 12</option></select>
    <select data-act="line" title="붙었을 때 선"><option value="none">선 없음</option><option value="column">선 열 폭</option><option value="full">선 전체폭</option></select>
    <span class="proto-state"></span>`;
  document.body.append(bar);

  const nav = document.querySelector(".mainnav");
  const render = () => {
    const [key, name] = VARIANTS[idx];
    root.dataset.nav = key;
    sticky ? root.setAttribute("data-sticky", "") : root.removeAttribute("data-sticky");
    set("proto-nav", key);
    set("proto-sticky", sticky ? "1" : null);
    root.dataset.stickyPad = pad;
    root.dataset.stickyLine = line;
    set("proto-sticky-pad", pad);
    set("proto-sticky-line", line);
    for (const [act, v] of [["pad", pad], ["line", line]]) {
      const el = bar.querySelector(`[data-act=${act}]`);
      el.value = v;
      el.disabled = !sticky;
    }
    updateStuck();
    bar.querySelectorAll("[data-key]").forEach(b => b.setAttribute("aria-pressed", String(b.dataset.key === key)));
    bar.querySelector(".proto-label").textContent = name;
    bar.querySelector("[data-act=sticky]").checked = sticky;
    // 상태를 드러낸다: 지금 뷰포트에서 내비게이션의 실제 크기.
    requestAnimationFrame(() => {
      const r = nav.getBoundingClientRect();
      bar.querySelector(".proto-state").textContent =
        `vw ${innerWidth} · nav ${Math.round(r.width)}×${Math.round(r.height)}` +
        (root.hasAttribute("data-stuck") ? " · 붙음" : "") +
        (root.scrollWidth > root.clientWidth ? " · 가로 넘침!" : "");
    });
  };

  // 위에 붙었는지. CSS만으로는 알 수 없어 스크롤 위치로 판정한다.
  function updateStuck() {
    const stuck = sticky && scrollY > 0 && nav.getBoundingClientRect().top <= 0.5;
    stuck ? root.setAttribute("data-stuck", "") : root.removeAttribute("data-stuck");
  }
  addEventListener("scroll", () => { const was = root.hasAttribute("data-stuck"); updateStuck(); if (was !== root.hasAttribute("data-stuck")) render(); }, { passive: true });

  const step = d => { idx = (idx + d + VARIANTS.length) % VARIANTS.length; render(); };
  bar.addEventListener("click", e => {
    const t = e.target.closest("button, input");
    if (!t) return;
    if (t.dataset.act === "prev") step(-1);
    else if (t.dataset.act === "next") step(1);
    else if (t.dataset.act === "sticky") { sticky = t.checked; render(); }
    else if (t.dataset.act === "pad" || t.dataset.act === "line") return;
    else if (t.dataset.key) { idx = VARIANTS.findIndex(([k]) => k === t.dataset.key); render(); }
  });
  bar.addEventListener("change", e => {
    if (e.target.dataset.act === "pad") { pad = e.target.value; render(); }
    if (e.target.dataset.act === "line") { line = e.target.value; render(); }
  });
  addEventListener("keydown", e => {
    if (e.target.closest("input, textarea, [contenteditable]")) return;
    if (e.key === "ArrowLeft") step(-1);
    if (e.key === "ArrowRight") step(1);
  });
  addEventListener("resize", render);
  render();
})();
