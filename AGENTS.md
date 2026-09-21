# AGENTS.md

이 저장소에서 작업하는 코딩 에이전트를 위한 안내다. `CLAUDE.md`도 이 파일을 가리킨다.

이 파일은 **지도**(어디에 무엇이 있고 무엇으로 돌리는가)와 **함정**(틀리면 조용히 망가지고 스스로는 알아낼 수 없는 것)만 담는다. 코드를 읽어 알 수 있는 것은 여기 없다.

- 결정의 이유는 `docs/adr/` — 아래 "어느 ADR을 언제 읽는가" 참고
- 에이전트 작업 절차는 `docs/agents/`
- 도메인 어휘는 `CONTEXT.md` — **탐색 전에 읽는다**

기술 스택: **Kirby CMS 5** (Plainkit 기반), PHP.

## 구조

### 디렉터리

- **kirby/**: Kirby CMS 코어 (v5.0.4)
- **site/**: 이 프로젝트의 파일
  - `templates/`: 페이지 종류마다 하나씩 있는 PHP 템플릿
  - `blueprints/`: 콘텐츠 필드와 패널 UI를 정의하는 YAML
  - `snippets/`: 재사용 조각 (`header.php`, `footer.php`, `blocks/heading.php`, `sitemap.php`)
  - `plugins/`: Kirby 플러그인
- **content/**: 플랫 파일 콘텐츠 (YAML 프런트매터 + Kirby Blocks JSON)
- **assets/**: CSS와 정적 파일 (`favicon.svg`, `fonts/` — 직접 올린 글꼴)
- **media/**: 자동 생성된 썸네일·가공 이미지 — 손대지 않는다
- **index.php**: 진입점 (로컬 개발 전용 — 운영에는 PHP가 없다)
- **scripts/build-static.php**: 정적 빌드. 모든 화면을 그려 `dist/`에 떨군다
- **static/**: `dist/` 루트로 그대로 복사되는 호스팅 설정 (`_headers`)
- **.github/workflows/deploy.yml**: 빌드 → Cloudflare Pages 배포

### 화면과 템플릿

| 템플릿 | URL | 블루프린트 |
|---|---|---|
| `home.php` | `/` | — |
| `blog.php` | `/blog` | `blog.yml` |
| `blogpost.php` | `/blog/*` | `blogpost.yml` |
| `projects.php` | `/projects` | `projects.yml` |
| `project.php` | `/projects/*` | `project.yml` |
| `about.php` | `/about` | `about.yml` |

### 콘텐츠

- **글** (`content/1_blog/N_slug/blogpost.txt`): `Title`, `Blocks`(블록 에디터 JSON), `Date`, `Tags`, `Description`(선택)
- **프로젝트** (`content/2_projects/N_slug/project.txt`): 이미지 중심 페이지
- 폴더 앞의 숫자가 정렬 순서와 노출을 결정한다 (`1_` = 목록에 나옴)
- `.png.txt` / `.jpg.txt` 사이드카 파일에 이미지 메타데이터가 들어간다
- 본문은 블록 에디터로 쓰고 템플릿에서 `$page->blocks()->toBlocks()`로 그린다. 블록 스니펫은 `site/snippets/blocks/`에 둔다
- 태그는 콤마로 이어붙인 문자열이고 `->tags()->split()`으로 나눈다
- 홈은 최신 글 5개와 프로젝트 4개를 `->children()->listed()->limit(N)`으로 보여준다
- 사이트맵은 `site/snippets/sitemap.php`

### CSS

- `assets/css/index.css`: 전역 스타일, CSS 커스텀 속성, 내비게이션, 공통 요소
- `assets/css/templates/*.css`: 템플릿별 스타일시트. `header.php`의 `css("@auto")`가 **템플릿 파일명과 같은 이름**을 자동으로 불러온다 (`blogpost.php` → `assets/css/templates/blogpost.css`). 그래서 여러 템플릿이 함께 쓰는 규칙은 `index.css`에 있어야 한다

### 플러그인

- **kirby-uniform** (`mzur/kirby-uniform ^5.6`): 스팸 가드가 붙은 폼 처리
- **kirby3-redirects** (`bnomei/kirby3-redirects ^5.1`): 패널에서 리다이렉트 관리
- **kirby-form** / **kirby-flash**: 폼·플래시 보조
- **seo** (`site/plugins/seo`, 직접 만듦): `header.php`가 쓰는 페이지 메서드 `metaTitle()`, `metaDescription()`, `metaExcerpt()`, `metaType()`, `metaImageFile()`, `metaCard()`
- **assets** (`site/plugins/assets`, 직접 만듦): Kirby의 `css` 컴포넌트를 덮어써서 스타일시트 URL에 `?v=<filemtime>`을 붙인다

## 함정

### `config.php`는 확장을 세 개만 읽는다

**Kirby는 `site/config/config.php`에서 `api`, `routes`, `hooks` 셋만 확장으로 읽는다** (`AppPlugins::extensionsFromOptions()`). `components`, `pageMethods`, `blueprints` 등 나머지는 **전부 플러그인에서 등록해야 한다**. `components`를 설정 파일에 써도 **조용히 무시된다** — 에러도 경고도 없고 기본 컴포넌트가 그냥 계속 돈다.

### 직접 만든 플러그인은 `.gitignore` 예외를 같은 커밋에 넣는다

`.gitignore`에 `/site/plugins/*`가 있다. Composer가 거기 설치하는 패키지를 걸러내기 위한 것이라, **새로 만든 로컬 플러그인은 기본적으로 git에 보이지 않는다.** 직접 쓴 플러그인마다 `!/site/plugins/<name>` 줄이 명시적으로 필요하다.

이건 조용히 실패하고 알아차리기 비싸다. Kirby는 모르는 `$page->foo()`를 에러가 아니라 **콘텐츠 필드 조회**로 해석한다 — 그래서 빠진 플러그인의 페이지 메서드는 예외를 던지지 않고 **빈 필드**를 돌려준다. 로컬에는 파일이 있으니 전부 통과하고, 프로덕션에서만 태그가 빈 채로 그려진다. `seo`가 정확히 이렇게 깨져서 나갔다 (`b07659b`에서 고침) — 모든 페이지의 `<title>`이 빈 채로 배포됐다.

배포 전 확인:

```bash
git ls-files site/plugins/<name>   # 파일 목록이 나와야 한다. 아무것도 안 나오면 안 된다
```

### 되풀이되는 실수들

- **`metaCard()`가 `header.php`가 부르는 메서드다.** `metaImageFile()`이 아니다
- **`h2`는 `--text-subhead`를 쓴다.** 제목은 화면당 `<h1>` 하나뿐이고 본문에 다시 나오지 않으므로, 본문 소제목은 `##`에서 시작한다 — 콘텐츠에 `#`을 쓰지 않는다
- **`--column` 위에 좌우 `padding`을 얹지 않는다.** 여백은 이미 폭 안에 들어 있다
- **형광펜 규칙은 `index.css` 한 곳에만 둔다.** 템플릿 CSS로 복제하지 않는다
- **출력 형식이 중요하면 `crop()`이 아니라 `thumb()`을 쓴다.** `crop()`은 `format` 옵션을 말없이 버린다

## 개발

```bash
composer start       # localhost:8000에 PHP 개발 서버
composer install     # 의존성 설치
composer update      # 의존성 갱신
php scripts/build-static.php   # dist/ 에 정적 빌드 — 운영에 나가는 것과 같은 결과물
```

PHP 요구 버전: `~8.2 || ~8.3 || ~8.4 || ~8.5`
개발 의존성: `laravel/pint` (PHP 포매터)

관리 화면은 `/panel` — 페이지, 초안, 리다이렉트, 업로드를 다룬다.

## 배포

운영은 Cloudflare Pages의 정적 사이트다 (ADR-0016). `main`에 들어오면 GitHub Actions(`.github/workflows/deploy.yml`)가 `scripts/build-static.php`를 돌려 `dist/`를 올리고, PR마다 미리보기 주소가 붙는다. `git push`는 GitHub에만 닿는다.

**이 저장소는 공개되어 있다.** 계정 ID, API 토큰, DNS 레코드 값을 추적되는 파일에 절대 쓰지 않는다. 토큰은 저장소 시크릿에만 있다.

### 정적 빌드의 함정

- **운영에서 요청 시점에 도는 PHP는 없다.** 컨트롤러에서 요청을 읽는 코드, 폼, 세션은 동작하지 않는다. `config.php`에 파일을 내보내는 라우트를 더하면 `build-static.php`의 라우트 목록에도 더한다 — 빌드 스크립트가 모르면 배포되지 않는다
- **미디어는 빌드가 그린 HTML에서 찾은 것만 나간다.** HTML에 주소가 찍히지 않는 파일은 `dist/`에 없다
- **캐시 수명은 `static/_headers`가 정한다.** 한 요청에 규칙이 둘 걸리면 헤더가 쉼표로 이어붙는다 — 경로가 겹치는 규칙을 쓰지 않는다. `assets/` 아래 이름이 고정된 파일을 새로 두면 여기에 줄을 더한다
- **`?v=`와 `lastmod`는 파일의 마지막 커밋 시각이다.** 워크플로가 체크아웃 뒤 수정시각을 git 이력으로 되돌린다. 로컬 빌드와 운영 빌드의 `?v=`가 다른 것은 그래서 정상이다

### 절차

```bash
gh pr merge <n> --merge --delete-branch   # main에 들어가면 워크플로가 배포한다
gh run watch                              # 배포 워크플로를 지켜본다
```

로컬에서 운영과 같은 결과물을 보려면 `php scripts/build-static.php` 후 `dist/`를 연다.

워크플로의 성공을 믿지 말고 실제 사이트로 확인한다:

```bash
curl -s https://massivevoid.com/ | grep -o 'assets/css[^"]*'
curl -sI https://massivevoid.com/blog | head -1                          # 200 — 308이면 canonical 전체가 리다이렉트다
curl -sI "https://massivevoid.com/assets/css/index.css?v=1" | grep -i cache-control   # immutable 하나만
```

## 어느 ADR을 언제 읽는가

작업을 시작하기 전에, 건드릴 영역에 해당하는 줄의 ADR을 읽는다.

- 페이지 제목·메타 태그·OG 태그 → `0004`, `0005`, `0011`
- 공유 카드 이미지 → `0006`
- 글자 크기·소제목 단계 → `0007`, `0008`
- 글꼴·글꼴 파일 → `0017`
- 본문 열 폭·반응형 분기 → `0012`
- 링크 hover·현재 화면 표시(형광펜) → `0014`
- 이미지를 눌러 크게 보기·자체 JS → `0015`
- 내비게이션 → `0009`
- 캐시·에셋 URL·`_headers` → `0010`, `0013`, `0016`
- 정적 빌드·배포·Cloudflare Pages → `0016`
- 이웃 글 → `0002`
- 정규 호스트 → `0003`
- 분석 → `0001`

## 에이전트 작업 절차

- **이슈 트래커**: 이슈와 스펙은 `flameware/kirby-blog`의 GitHub 이슈로 관리하고 `gh` CLI를 쓴다. `docs/agents/issue-tracker.md`
- **트리아지 라벨**: 다섯 개의 표준 역할을 GitHub 라벨 이름으로 그대로 쓴다. `docs/agents/triage-labels.md`
- **도메인 문서**: 단일 컨텍스트 — 루트에 `CONTEXT.md` 하나와 `docs/adr/` 하나. `docs/agents/domain.md`
