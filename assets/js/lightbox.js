/**
 * 라이트박스 — 이미지를 누르면 화면을 덮고 나머지가 물러난다.
 *
 * 본문 열은 넓은 화면에서 46.7vw다(ADR-0012). 4132px짜리 원본이 706px로 눌려
 * 표시되므로, 독자는 화면에 담긴 것보다 훨씬 큰 그림을 보면서도 볼 방법이 없었다.
 * 여기서 하는 일은 그 폭 제한을 풀고 원본을 보여 주는 것이다. 본문에는 열 폭에 맞춰
 * 줄인 파일이 내려와 있으므로(#26), 라이트박스는 원본을 따로 받는다.
 *
 * 근거와 버린 대안: docs/adr/0015-own-javascript.md, docs/adr/0018-responsive-images.md
 */
(() => {
    /**
     * 클릭을 위임으로 받는 이유.
     *
     * Kirby 기본 블록 스니펫(image, gallery)을 복제하면 업스트림이 고쳐져도
     * 우리만 뒤처진다. 지금 덮어쓴 스니펫은 heading 하나뿐이고 그대로 두고 싶다.
     * 문서 한 곳에서 클릭을 받으면 이미지 블록·갤러리·작업 이미지 세 경로가
     * 마크업을 한 줄도 안 건드리고 같은 규칙을 받는다.
     *
     * 선택자가 둘인 것은 마크업이 둘이어서다. 글의 이미지 블록과 갤러리는 블록
     * 스니펫이 그리므로 <figure> 안에 있고, 프로젝트의 작업 이미지는 템플릿이
     * 직접 그리는 .project-gallery 안에 있다. `main img`로 한 번에 잡을 수도 있지만,
     * 그러면 나중에 본문에 들어올 장식용 이미지까지 눌리게 된다.
     */
    const TARGET = "figure img, .project-gallery img";

    /**
     * 대화상자는 하나를 만들어 계속 쓴다. 이미지마다 만들면 갤러리가 있는 글에서
     * 열 때마다 DOM이 늘고, 닫힌 것들이 뒤에 쌓인다.
     */
    const dialog = document.createElement("dialog");

    /**
     * <dialog>를 모르는 브라우저에서는 아무것도 하지 않는다.
     *
     * createElement는 모르는 이름에도 요소를 돌려주므로(HTMLUnknownElement)
     * 존재만으로는 판단할 수 없다. showModal이 실제로 함수인지를 본다.
     * 여기서 물러나면 아래의 클래스도 안 붙어 표시가 나타나지 않는다.
     */
    if (typeof dialog.showModal !== "function") {
        return;
    }

    dialog.className = "lightbox";
    const full = document.createElement("img");
    dialog.append(full);
    document.body.append(dialog);

    document.addEventListener("click", (event) => {
        /**
         * 닫기: 아무 데나 누르면 닫힌다 — 배경이든 이미지든.
         *
         * 눈에 보이는 닫기(×) 버튼을 두지 않은 것은 이 사이트에 버튼이라는 부품이
         * 없기 때문이기도 하지만, 모바일에서 이미지가 화면을 거의 채우면 배경이
         * 눌릴 자리가 몇 픽셀 안 남아 배경 클릭만으로는 실제로 잘 안 닫혀서다.
         * "아무 데나"는 발견할 필요조차 없는 규칙이라 실패할 수 없다.
         */
        if (dialog.open === true) {
            dialog.close();
            return;
        }

        const image = event.target.closest(TARGET);

        if (image === null) {
            return;
        }

        /**
         * 링크 안의 이미지는 라이트박스 대상이 아니다 — 링크가 이긴다.
         *
         * 두 가지가 여기 걸린다. 하나는 목록의 프로젝트 카드다. projects.php와
         * home.php가 카드를 <a><figure><img></figure></a>로 그리므로 figure img에
         * 걸려든다 — 막지 않으면 카드를 눌러도 프로젝트로 가지 않고 썸네일이
         * 라이트박스로 열린다. 다른 하나는 image 블록의 link 필드다. 필자가 링크를
         * 적어 넣었다면 그건 라이트박스보다 분명한 의사표시다.
         *
         * 이 판정이 CSS의 커서 규칙과 같은 조건이어야 한다(index.css). 한쪽만
         * 고치면 "손가락 커서인데 안 눌리는" 상태가 생긴다.
         */
        if (image.closest("a[href]") !== null) {
            return;
        }

        /**
         * 원본을 연다. 본문에 그려진 파일(currentSrc)은 열 폭에 맞춰 줄인 것이라,
         * 그걸 쓰면 라이트박스가 작은 그림을 키워 보여 주고 어디서도 오류가 나지 않는다.
         *
         * 원본 주소는 스니펫이 data-full에 적어 둔다(site/snippets/image.php). 주소를
         * 여기서 조립하지 않는 것은 정적 빌드가 HTML에 찍힌 파일만 배포하기 때문이다.
         * data-full이 없는 것 — 바깥 주소의 이미지 — 은 줄인 적이 없으니 그린 것을 쓴다.
         */
        full.src = image.dataset.full || image.currentSrc || image.src;
        full.alt = image.alt;

        /**
         * show()가 아니라 showModal()이다. Esc로 닫기, 초점 가둠, 배경의
         * inert 처리가 여기서 따라온다 — 직접 만들면 셋 다 손으로 다시 해야 한다.
         */
        dialog.showModal();
    });

    /**
     * 닫힌 뒤에는 이미지를 비운다. 남겨 두면 다음에 열 때 이전 이미지가 한 프레임
     * 먼저 보인다.
     */
    dialog.addEventListener("close", () => {
        full.removeAttribute("src");
        full.removeAttribute("alt");
    });

    /**
     * 진행적 향상 — 표시와 동작은 한 몸이다.
     *
     * 커서와 hover 표시는 이 클래스 아래에서만 켜진다(index.css). 스크립트가
     * 실패하면 클래스가 안 붙고, 그러면 누를 수 있다는 표시도 나타나지 않는다.
     * 표시가 있으면 반드시 동작하고, 동작이 없으면 표시도 없다.
     *
     * 이 줄이 **맨 마지막**인 것이 핵심이다. 위쪽 어디서 물러나거나 예외가 나도
     * 표시만 켜진 상태가 남지 않는다 — 켤 수 있다는 것은 이미 다 걸었다는 뜻이다.
     */
    document.documentElement.classList.add("has-lightbox");
})();
