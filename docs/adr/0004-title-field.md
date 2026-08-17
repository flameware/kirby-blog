# ADR-0004: 제목은 title 필드 하나이며, 본문에 두지 않는다

- **상태:** 채택
- **날짜:** 2026-08-17
- **관련:** [#3](https://github.com/flameware/kirby-blog/issues/3)

## 맥락

`CONTEXT.md`는 **제목**을 "글이나 프로젝트를 부르는 이름. 한 편에 정확히 하나만 존재한다"고 정의해 왔다. 코드는 그렇지 않았다.

블로그 글의 제목은 본문 blocks의 **첫 heading 블록**에 있었고, Kirby의 `title` 필드에도 값이 따로 있었다. 즉 **한 편에 제목이 두 개** 있었고, 18편 중 10편에서 두 값이 서로 달랐다:

| 슬러그 | 화면(heading) | `title` 필드 |
|---|---|---|
| `about-mclaren` | 맥라렌? 매클래런? | About Mclaren |
| `think-claude-code` | 단상 - Claude Code | think-claude-code |
| `building-blog-again-two` | 블로그를 또 새로 만들기 (2편) | Building Blog Again Two |

`title` 필드가 죽은 값도 아니었다. `postnav.php`의 이전/다음 링크가 `title`·`aria-label`에 그것을 쓰고 있어서, 스크린리더로는 "다음: About Mclaren"이라 안내하고 도착하면 "맥라렌? 매클래런?"이 보이는 상태였다.

프로젝트는 사정이 달랐다. heading 블록이 0개고 `title` 필드가 유일한 제목이었지만, `project.php`가 제목을 **화면에 렌더하지 않아** 상세 화면에 제목이 아예 없었다.

## 결정

**제목은 `title` 필드 하나다. 본문 blocks에는 제목을 두지 않는다.**

- 불일치 10편은 **화면의 heading 값을 정본으로** 채택해 `title` 필드를 덮었다. 나머지 8편은 두 값이 같아 그대로다.
- 18편 전부에서 첫 heading 블록을 본문에서 제거했다.
- `blogpost.php` / `project.php`가 `$page->title()`을 `<h1 class="post-title">`으로 렌더한다. 두 템플릿이 같은 마크업을 쓰므로 `.post-header` 계열 스타일은 `assets/css/index.css`로 옮겼다 — 템플릿별 CSS는 `css("@auto")`로 한쪽만 로드되기 때문이다(ADR-0002와 같은 이유).
- `blog.php` / `home.php`가 제목을 얻으려고 블록을 순회하던 코드를 `$blogpost->title()`로 교체했다.
- `blogpost.yml` / `project.yml`의 `fields:`에 `title`을 `required: true`로 노출해, 패널 폼 안에서 제목을 편집하게 했다.

## 근거

**`title` 필드가 아니라 heading 값을 정본으로 고른 이유:** `title` 쪽 값은 Kirby가 페이지 생성 시 슬러그에서 자동으로 채운 흔적이 뚜렷했다(`think-claude-code`, `Driver - Sebastian Vettel-01`, `Building Blog Again Two`). heading 쪽은 "맥라렌? 매클래런?", "메가데스의 신곡과 (이상한 나라의) 메탈 뮤직비디오들"처럼 실제로 쓴 제목이고, 독자가 보고 있던 것도 그쪽이다. 자동 생성값을 사람이 쓴 값보다 우선할 이유가 없다.

**URL은 건드리지 않는다:** Kirby에서 슬러그는 폴더명이라 `title`과 독립이다. 제목 10개를 바꿔도 기존 링크는 그대로 산다.

**heading 블록 타입과 `blocks/heading.php` 스니펫은 남겼다:** 마이그레이션 전까지 heading 블록은 글당 정확히 1개, 항상 첫 블록이라 사실상 제목 전용이었고, 그래서 스니펫은 렌더된 적이 없는 죽은 코드였다. 제목이 본문에서 빠진 지금은 heading 블록이 **본문 소제목을 쓰는 정상 경로**가 된다. 막을 이유가 없다.

**본문 소제목의 헤딩 레벨은 이번에 손대지 않았다:** 소제목은 markdown 블록 안의 `###`(→`<h3>`)로 65곳에 쓰여 있어, 제목이 `<h1>`이 되면서 문서 아웃라인이 h1 → h3로 h2를 건너뛴다. 이를 맞추려면 본문 65곳을 치환하고 `--text-h2`가 `.post-title`과 같은 크기라 CSS까지 함께 조정해야 한다. 제목 일원화(첫 블록만 건드림)와 본문 전면 치환은 되돌릴 단위가 다르므로 분리했다. → [#7](https://github.com/flameware/kirby-blog/issues/7)

## 결과

- **새 글을 쓸 때 본문 맨 위에 제목을 다시 넣으면 제목이 두 번 보인다.** 패널 폼의 `제목` 필드가 유일한 자리다.
- 이슈 [#6](https://github.com/flameware/kirby-blog/issues/6)(글마다 고유한 `<title>`)의 선행 조건이 풀렸다. 이제 `header.php`에 `$page->title()`을 넘기면 된다.
- `postnav.php`는 코드 변경 없이 올바른 제목을 안내하게 됐다. 링크가 가리키던 값이 정본이 되었기 때문이다.
