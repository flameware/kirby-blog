# ADR-0013: 캐시 수명은 URL이 내용을 따라가는지로 갈린다

- **상태:** 채택
- **날짜:** 2026-08-18
- **관련:** [ADR-0010](0010-asset-cache-busting.md)

## 맥락

서버가 **어떤 응답에도 `Cache-Control`을 보내지 않았다.** HTML에는 검증자조차 없고, 자산에는 `ETag`·`Last-Modified`만 있었다. 헤더가 없으면 브라우저는 휴리스틱으로 신선도를 스스로 정하는데, 보통 `(현재 − Last-Modified)`의 10%다.

그 결과가 **거꾸로** 나온다. 6월에 만든 `favicon.svg`는 닷새쯤 확인 없이 쓰이고, 방금 배포한 `index.css`는 몇 분이면 만료된다. 오래된 파일일수록 오래 붙들리는데, 정작 오래된 파일이 안 바뀌는 파일이다.

[ADR-0010](0010-asset-cache-busting.md)에서 스타일시트 URL에 `?v=<filemtime>`을 붙이면서 조건이 갖춰졌다. URL이 내용을 따라가면 캐시가 낡아도 회수할 일이 없으므로, 이제 `max-age`를 길게 주는 것이 안전하다.

**이건 성능 작업이지 정확성 작업이 아니다.** ADR-0010이 다룬 iOS Safari의 낡은 CSS 문제를 이 변경이 막아주지는 않는다 — 열려 있던 탭을 복원할 때 네트워크에 아무것도 묻지 않는 건 헤더와 무관한 동작이고, 그건 `?v=`가 이미 해결했다.

## 결정

**`.htaccess`의 기존 `<IfModule mod_headers.c>` 블록에 계층별 `Cache-Control`을 준다.** 계층은 셋이고, 가르는 기준은 하나다 — **URL이 내용을 따라가는가.**

| 계층 | 값 | 근거 |
|---|---|---|
| HTML | `no-cache` | 자산 URL을 담고 있는 문서다 |
| `?v=` 자산 · `/media/**` | `public, max-age=31536000, immutable` | 내용이 바뀌면 주소가 바뀐다 |
| 버전 없는 `/assets/**` | `public, max-age=86400` | 회수 가능한 하루 |

`.htaccess`는 `<Location>`·`<Directory>`를 쓸 수 없으므로, 그 파일이 이미 쓰고 있는 `expr=` 관용구를 따랐다.

`/media/`에 `immutable`을 거는 근거는 URL이 이미 그렇게 생겼다는 것이다 — `…/random-walker/ef17dc8e41-1782744265/randomwalker2.jpg`. 해시와 시각이 경로에 박혀 있어 이미지가 바뀌면 주소가 바뀐다. `?v=`와 같은 원리다.

## 근거

**HTML은 `no-cache`다.** 긴 `max-age`는커녕 잠깐의 유예도 주지 않는다. 이 문서가 낡으면 옛 `?v=` 값을 가리키게 되고, 그 순간 ADR-0010의 구조 전체가 무의미해진다. 캐시 계층의 뿌리라서 여기만은 매번 확인한다.

**`immutable`은 회수가 불가능하다는 뜻이다.** 잘못된 파일이 그 헤더를 달고 나가면 1년간 손쓸 방법이 없다 — URL을 바꾸는 것 말고는. 계층을 나눈 이유가 전부 이것이다. `favicon.svg`·`og-default.png`처럼 이름이 고정된 파일에는 절대 붙이지 않고, 하루짜리 `max-age`만 준다.

**자리는 이 저장소 안이다.** `.htaccess`가 추적되고 있고 서버에서 실제로 먹고 있다 — 응답의 `X-Content-Type-Options: nosniff`와 CSS의 gzip이 전부 이 파일에서 나온다. `git push origin main`으로 배포되는 변경이다. (ADR-0010의 "Apache 설정이라 이 저장소 바깥의 일"이라는 문장은 이 점에서 틀렸고, 같이 고쳤다.)

**되돌리기 창이 좁다는 것을 알고 한다.** `.htaccess` 문법 오류는 사이트 전체 500이고, `apachectl configtest`는 `.htaccess`를 읽지 않아 사전 검증이 안 된다. 그래서 배포 직후 네 종류의 헤더를 즉시 확인하는 것을 이 변경의 일부로 둔다.

## 결과

배포 직후 실제 사이트에서 확인한 값이다.

| 요청 | `Cache-Control` |
|---|---|
| `https://massivevoid.com/` | `no-cache` |
| `…/assets/css/index.css?v=1787018532` | `public, max-age=31536000, immutable` |
| `…/media/pages/projects/random-walker/…/randomwalker2.jpg` | `public, max-age=31536000, immutable` |
| `…/assets/favicon.svg` | `public, max-age=86400` |

- **og 이미지를 바꾸면 반영이 하루 늦는다.** SNS 쪽 캐시는 어차피 별개 문제라 실질 차이는 작다.
- **HTML에 검증자를 붙이는 것은 하지 않았다.** Kirby 출력에 `ETag`·`Last-Modified`가 없어 `no-cache`가 곧 전체 재전송이다. 조건부 요청으로 줄일 여지가 있지만 별개 문제이고, 여기에 섞으면 검증이 흐려진다.
- **`/panel`에는 규칙을 두지 않았다.** 운영 서버에서 비활성화되어 있다.
- **JS 계층은 따로 두지 않았다.** 이 사이트에 스크립트 파일이 없고, 생기면 `?v=` 계층이 그대로 받는다.
