# yesf 인증 서비스

mobile service의 사용자 인증을 위한 서비스 API입니다.

## 요구사항

- docker 최신버전
- docker-composer
- git

## 용어

- `project-dir`: git에서 checkout한 프로젝트 디렉토리.
- `project-root-dir`: 각 `project-dir`이 저장된 상위 디렉토리.

## 개발환경 구성

### 소스코드 다운로드

```bash
cd <project-root-dir>
git clone ssh://git@git.bankmedia.co.kr:10022/yesf/backend-api-auth.git
```

### 환경변수 디렉토리 설정 (공통)

```bash
cd <project-root-dir>
mkdir secrets
cd secrets
touch JWT_SECRET  # JWT 토큰의 비밀키
```

### 도커 네트워크 드라이버 생성 (공통)

```bash
docker network create yesf
```

### 개발용 도커 환경 준비

1. 이미지 빌드

```bash
cd <project-dir>
docker build -t backend-api-auth .
```

1. 로컬 테스트용 개발환경 컨테이너 실행

```bash
cd <project-dir>
docker-compose up
```

## API 기능 설명

| HTTP METHOD      | POST        | GET | PUT | DELETE |
| ---------------- | ----------- | --- | --- | ------ |
| /v1/auth/login   | 로그인      | -   | -   | -      |
| /v1/auth/refresh | 토큰 재발급 | -   | -   | -      |

## APi 상세 설명

### 로그인

POST /v1/auth/login

- params
  - userid
  - userpw
  - userpw_auto
- response

```json
{
  "access_token": "...",
  "refresh_token": "..."
}
```

### 토큰 재발급

POST /v1/auth/refresh

- header
  - Authorization: Bearer $token
- response

```json
{
  "access_token": "...",
  "refresh_token": "..."
}
```

## 에러코드

| 에러메세지             | 에러코드 | 설명                      |
| :--------------------- | -------- | :------------------------ |
| ERROR_LOGIN_FAILED     | 101      | 로그인 실패               |
| ERROR_EXPIRED_TOKEN    | 102      | 토큰 만료                 |
| ERROR_CREATED_TOKEN    | 103      | 토큰 생성 실패            |
| ERROR_VALIDATE_TOKEN   | 104      | 토큰 검증 실패            |
| ERROR_MEMBER_NOT_FOUND | 203      | 회원 정보 없음            |
| ERROR_INVALID_PARAM    | 901      | 파라미터 유효성 검증 실패 |
| ERROR_SERVER_UNKNOWN   | 999      | 알 수 없는 오류           |
