// PROTOTYPE — 버린다. prototype/tag-variants 브랜치에만 있다.
// 떠 있는 전환 막대. 상태는 localStorage에 두어 글을 옮겨도 유지된다.
// 깜빡임을 막는 초기 적용은 header.php의 인라인 스크립트가 먼저 한다.
(() => {
  const VARIANTS = [
    ["0", "현재 — 진한 알약"],
    ["a", "A — # 텍스트"],
    ["b", "B — 구분선 + 쉼표 한 줄"],
    ["c", "C — 옅은 테두리 알약"],
    ["d", "D — 글 머리로 올림"],
  ];
  const root = document.documentElement;
  const get = (k, d) => { try { return localStorage.getItem(k) ?? d; } catch { return d; } };
  const set = (k, v) => { try { v === null ? localStorage.removeItem(k) : localStorage.setItem(k, v); } catch {} };

  const tags = document.querySelector(".tags");
  if (!tags) return;
  const home = { parent: tags.parentNode, next: tags.nextSibling };
  const header = document.querySelector(".post-header");
  const words = [...tags.querySelectorAll("li")].map(li => li.textContent.trim());

  // ?tags=a&links=1 이 localStorage보다 앞선다 — 주소로 시안을 공유할 수 있게.
  const q = new URLSearchParams(location.search);
  let idx = Math.max(0, VARIANTS.findIndex(([k]) => k === (q.get("tags") ?? get("proto-tags", "a"))));
  let links = (q.get("links") ?? get("proto-tag-links", "")) === "1";

  const bar = document.createElement("div");
  bar.className = "proto-bar";
  bar.innerHTML = `
    <button data-act="prev" aria-label="이전 시안">←</button>
    ${VARIANTS.map(([k]) => `<button data-key="${k}">${k === "0" ? "현재" : k.toUpperCase()}</button>`).join("")}
    <button data-act="next" aria-label="다음 시안">→</button>
    <span class="proto-label"></span>
    <span class="proto-sep"></span>
    <label><input type="checkbox" data-act="links"> 링크로</label>
    <span class="proto-state"></span>`;
  document.body.append(bar);

  const render = () => {
    const [key, name] = VARIANTS[idx];
    key === "0" ? delete root.dataset.tags : (root.dataset.tags = key);
    links ? root.setAttribute("data-tag-links", "") : root.removeAttribute("data-tag-links");
    set("proto-tags", key);
    set("proto-tag-links", links ? "1" : null);
    const u = new URL(location); u.searchParams.set("tags", key); links ? u.searchParams.set("links", "1") : u.searchParams.delete("links");
    history.replaceState(null, "", u);

    // 링크로 보기: 태그 페이지가 생겼다고 치고 <a><span>으로 감싼다(ADR-0014의 마커 상자).
    tags.querySelectorAll("li").forEach((li, i) => {
      li.innerHTML = links ? `<a href="#" onclick="return false"><span>${words[i]}</span></a>` : words[i];
    });

    // D만 위치가 다르다.
    if (key === "d" && header) header.append(tags);
    else home.parent.insertBefore(tags, home.next);

    bar.querySelectorAll("[data-key]").forEach(b => b.setAttribute("aria-pressed", String(b.dataset.key === key)));
    bar.querySelector(".proto-label").textContent = name;
    bar.querySelector("[data-act=links]").checked = links;
    // 상태를 드러낸다: 태그 덩어리의 실제 크기와 줄 수.
    requestAnimationFrame(() => {
      const r = tags.getBoundingClientRect();
      const tops = new Set([...tags.querySelectorAll("li")].map(li => Math.round(li.getBoundingClientRect().top)));
      bar.querySelector(".proto-state").textContent =
        `vw ${innerWidth} · 태그 ${words.length}개 · ${tops.size}줄 · ${Math.round(r.height)}px` +
        (root.scrollWidth > root.clientWidth ? " · 가로 넘침!" : "");
    });
  };

  const step = d => { idx = (idx + d + VARIANTS.length) % VARIANTS.length; render(); };
  bar.addEventListener("click", e => {
    const t = e.target.closest("button, input");
    if (!t) return;
    if (t.dataset.act === "prev") step(-1);
    else if (t.dataset.act === "next") step(1);
    else if (t.dataset.act === "links") { links = t.checked; render(); }
    else if (t.dataset.key) { idx = VARIANTS.findIndex(([k]) => k === t.dataset.key); render(); }
  });
  addEventListener("keydown", e => {
    if (e.target.closest("input, textarea, [contenteditable]")) return;
    if (e.key === "ArrowLeft") step(-1);
    if (e.key === "ArrowRight") step(1);
  });
  addEventListener("resize", render);
  render();
})();
