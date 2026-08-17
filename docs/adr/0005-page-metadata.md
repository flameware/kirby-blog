# ADR-0005: 메타데이터는 페이지가 스스로 답한다

- **상태:** 채택
- **날짜:** 2026-08-17
- **관련:** [#6](https://github.com/flameware/kirby-blog/issues/6), [ADR-0004](0004-title-field.md)

## 맥락

45개 페이지 전부가 같은 `<title>`(`Massive Void | 손성기`)을 내보내고 있었다. `header.php`는 `$title`을 받아 그대로 출력하는데, `blogpost.php`·`project.php`·`about.php`·`home.php`·`default.php`가 전부 그 고정 문자열을 넘겼다. 고유한 값을 넘긴 템플릿은 `blog.php`와 `projects.php` 둘뿐이었다.

`meta name="description"`은 0개, `og:*`도 0개였다. 검색 결과에서 발행된 글 19편이 모두 같은 제목으로 표시되고, 공유 카드에는 쓸 요약이 없었다.

색인 문제는 아니었다 — 미색인 14건은 www 호스트 중복이 원인이었고 [ADR-0003](0003-canonical-host.md)에서 처리했다. 이건 순위와 클릭률의 문제다.

선행 조건은 [ADR-0004](0004-title-field.md)였다. 제목이 본문 첫 heading 블록에 있던 동안에는 `<title>`에 쓸 값을 어디서 읽을지 정할 수 없었다. 제목이 `title` 필드 하나로 정리되면서 풀렸다.

## 결정

**메타데이터를 만드는 일은 템플릿이 아니라 페이지의 몫이다.** 템플릿은 `snippet('header')`를 인자 없이 부르고, `header.php`가 페이지에 직접 묻는다.

`site/plugins/seo`가 네 개의 페이지 메서드를 더한다:

- **`metaTitle()`** — `{제목} | Massive Void`. 홈은 사이트 이름 자체가 제목이므로 접미사 없이 `site.txt`의 `DisplayTitle`을 쓴다.
- **`metaDescription()`** — `description` 필드 → 본문 첫 문단 → 사이트 기본 요약 순으로 찾는다.
- **`metaExcerpt()`** — 본문 blocks에서 첫 산문 블록(`markdown`/`text`)을 찾아 heading을 걷어내고 160자로 자른다.
- **`metaType()`** — `blogpost`/`project`만 `article`, 나머지는 `website`.

`description` 필드를 `blogpost.yml`·`project.yml`·`about.yml`·`blog.yml`·`projects.yml`과 `site.yml`에 노출했다. Open Graph는 `og:title`·`og:description`·`og:url`·`og:type`·`og:site_name`·`og:locale`과 `twitter:card=summary`까지다.

## 근거

**템플릿이 제목을 넘기지 않게 한 이유:** 고정 문자열 6개가 6개 파일에 흩어져 있었던 것이 이 버그의 형태 그대로다. 제목을 넘길 수 있는 구멍이 남아 있으면 새 템플릿이 또 같은 실수를 한다. 페이지가 스스로 답하면 새 템플릿은 아무것도 하지 않아도 옳은 값을 얻는다.

**요약을 필드와 자동 추출 둘 다 둔 이유:** 필드만 두면 19편을 일일이 손대기 전까지 아무 효과가 없다. 자동 추출만 두면 첫 문단이 요약으로 부적절한 글을 고칠 방법이 없다. 필드를 우선하고 비었을 때 추출로 메우면, 손대지 않은 글도 즉시 요약을 갖고 중요한 글만 골라 다듬을 수 있다.

**소셜 카드 이미지는 범위에서 뺐다:** 기본 카드 이미지를 새로 디자인해야 하고, 글에는 대표 이미지 개념이 아직 없다. `twitter:card`를 `summary`로 둔 것도 그래서다 — `summary_large_image`는 이미지가 있어야 의미가 있다. 되돌릴 단위가 다르므로 분리했다.

**`{제목} | Massive Void | 손성기`를 쓰지 않은 이유:** 기존 `DisplayTitle`을 그대로 접미사로 쓰면 일관되지만, 한국어 제목 다수가 60자 제한을 넘겨 검색 결과에서 제목 뒷부분이 잘린다. 잘리는 건 접미사가 아니라 제목이다.

**`<html lang>`을 `ko`로 고쳤다:** 본문이 전부 한국어인데 `en`이었다. `og:locale`을 `ko_KR`로 넣으면서 같은 값을 두 곳이 다르게 주장하게 되므로 함께 맞췄다.

**목록 두 곳(`/blog`, `/projects`)에는 요약을 직접 써 넣었다:** 본문 blocks가 없어 자동 추출이 되지 않고, 사이트 기본 요약을 물려받으면 세 페이지가 같은 설명을 공유한다.

## 결과

- 45개 페이지 전부가 서로 다른 `<title>`과 `description`을 갖는다. 중복 0건.
- 새 글을 쓸 때 요약을 비워 두어도 된다. 본문 첫 문단이 자동으로 쓰인다.
- 소셜 카드에는 아직 이미지가 없다. `summary` 카드는 텍스트만 보여준다. → 별도 이슈
- `$title`을 받던 `header.php`의 인자는 사라졌다. 템플릿이 `snippet('header', ['title' => ...])`를 부르면 그 값은 무시된다.
