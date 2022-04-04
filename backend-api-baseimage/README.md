# yesfile base image

mobile service의 base image입니다.

yesfile mobile API service는 해당 base image를 사용합니다.


## version

base image의 버전이 업그레이드 되면 tag를 이용해서 version 관리를 합니다.

* registry.bankmedia.co.kr/yesfile/backend-api-baseimage:7.2-apache-0.2
* registry.bankmedia.co.kr/yesfile/backend-api-baseimage:7.2-apache-0.1
* registry.bankmedia.co.kr/yesfile/backend-api-baseimage:7.2-apache


## 요구사항

* docker 최신버전
* docker-composer
* git

## 개발환경 구성

### 소스코드 다운로드
``` bash
$ cd <project-root-dir>
$ git clone ssh://git@git.bankmedia.co.kr:10022/yesfile/backend-api-baseimage.git
```

### 도커 네트워크 드라이버 생성 (공통)
```bash
$ docker network create yesfile
```

### 개발용 도커 환경 준비
1. 이미지 빌드
``` bash
$ docker build -t backend-api-baseimage .
```

1. 로컬 테스트용 개발환경 컨테이너 실행
``` bash
$ docker-compose up
```

