# yesfile 로깅 서비스

mobile service의 로깅을 위한 서비스입니다.

logs 서비스를 실행하면 로컬 개발환경에서 해당 서비스의 표준출력으로 모든 로그가 출력됩니다.

## 요구사항

* docker 최신버전
* docker-composer
* git

## 개발환경 구성

### 소스코드 다운로드
``` bash
$ cd <project-root-dir>
$ git clone ssh://git@git.bankmedia.co.kr:10022/yesfile/backend-api-logs.git
```

### 도커 네트워크 드라이버 생성 (공통)
```bash
$ docker network create yesfile
```

### 개발용 도커 환경 준비
1. 이미지 빌드
``` bash
$ docker build -t backend-api-logs .
```

1. 로컬 테스트용 개발환경 컨테이너 실행
``` bash
$ docker-compose up
```

