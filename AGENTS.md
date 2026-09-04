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
- **assets/**: CSS와 정적 파일 (`favicon.svg`)
- **media/**: 자동 생성된 썸네일·가공 이미지 — 손대지 않는다
- **index.php**: 진입점

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
```

PHP 요구 버전: `~8.2 || ~8.3 || ~8.4 || ~8.5`
개발 의존성: `laravel/pint` (PHP 포매터)

관리 화면은 `/panel` — 페이지, 초안, 리다이렉트, 업로드를 다룬다.

## 배포

**이 저장소는 공개되어 있다.** 배포 호스트, 베어 저장소 경로, 훅 내부, 웹 루트, 자격 증명을 추적되는 파일에 절대 쓰지 않는다 — 셋업 문서가 `.gitignore`에 있는 이유다. 실제 주소는 필요한 시점에 `git remote -v`에서 읽는다.

### `origin`에는 push URL이 두 개다

`origin`은 GitHub에서 fetch하지만 **GitHub와 배포 서버 양쪽으로 push한다.** `git push` 한 번이 둘 다에 닿는다.

```bash
git remote -v   # fetch: GitHub · push: GitHub + 배포 서버
```

자주 무는 순서대로:

- **`main`을 push하면 배포된다.** 서버 쪽 receive 훅이 `main`을 체크아웃하고 `composer install`을 돌린다. push 출력에 `>>> Deploying main` … `>>> Done`이 보이는데, 그게 배포 로그다 — 읽는다. `main`만 배포되고, 기능 브랜치를 push하는 건 안전하며 운영에 아무 영향이 없다
- **`gh pr merge --delete-branch`는 GitHub만 치운다.** 브랜치가 배포 서버에 남아 쌓인다. 대신 `git push origin --delete <branch>`를 쓴다 — 양쪽에 닿는다
- **배포 실패는 push 실패가 아니라 `remote:` 출력으로 나타난다.** 서버 쪽 단계가 에러를 내도 push 자체는 성공할 수 있다

### 절차

```bash
gh pr merge <n> --merge         # GitHub에서 머지
git checkout main && git pull   # 로컬 main을 fast-forward
git push origin main            # 배포 — remote: 출력을 지켜본다
git push origin --delete <branch>   # 양쪽 원격에서 브랜치 정리
```

push를 믿지 말고 실제 사이트로 확인한다:

```bash
curl -s https://massivevoid.com/ | grep -o 'assets/css[^"]*'
```

캐시 수명은 추적되는 `.htaccess`가 정한다. **`.htaccess` 문법 오류는 사이트 전체 500이고 `apachectl configtest`는 이 파일을 읽지 않는다** — 배포 직후 `curl -I`로 확인한다. 규칙의 이유는 ADR-0010, 0013.

## 어느 ADR을 언제 읽는가

작업을 시작하기 전에, 건드릴 영역에 해당하는 줄의 ADR을 읽는다.

- 페이지 제목·메타 태그·OG 태그 → `0004`, `0005`, `0011`
- 공유 카드 이미지 → `0006`
- 글자 크기·소제목 단계 → `0007`, `0008`
- 본문 열 폭·반응형 분기 → `0012`
- 링크 hover·현재 화면 표시(형광펜) → `0014`
- 내비게이션 → `0009`
- 캐시·에셋 URL·`.htaccess` → `0010`, `0013`
- 이웃 글 → `0002`
- 정규 호스트 → `0003`
- 분석 → `0001`

## 에이전트 작업 절차

- **이슈 트래커**: 이슈와 스펙은 `flameware/kirby-blog`의 GitHub 이슈로 관리하고 `gh` CLI를 쓴다. `docs/agents/issue-tracker.md`
- **트리아지 라벨**: 다섯 개의 표준 역할을 GitHub 라벨 이름으로 그대로 쓴다. `docs/agents/triage-labels.md`
- **도메인 문서**: 단일 컨텍스트 — 루트에 `CONTEXT.md` 하나와 `docs/adr/` 하나. `docs/agents/domain.md`
