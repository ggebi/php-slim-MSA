# yesfile 인증 서비스

mobile service의 사용자 인증을 위한 서비스 API입니다.

## 요구사항

* docker 최신버전
* docker-composer
* git

## 개발환경 구성


### 소스코드 다운로드
``` bash
$ git clone ssh://git@git.bankmedia.co.kr:10022/yesfile/backend-api-configs.git
```


### 개발용 도커 환경 준비

1. 도커 네트워크 설정
```bash
$ docker network create yesfile
```

2. secrets 환경설정
> 파일의 위치는 프로젝트의 상위 디렉토리에 secrets 입니다.

| FILE | DESCRIPTION |
| ---- | ----------- |
| JWT_SECRET | JWT 토큰의 비밀키 |

3. 이미지 빌드
``` bash
$ docker build -t backend-api-configs .
```

4. 로컬 테스트용 개발환경 컨테이너 실행
``` bash
$ docker-compose up
```

## API 기능 설명
| HTTP METHOD | POST | GET | PUT | DELETE |
| ----------- | ---- | --- | --- | ------ |
| /v1/configs/versions | | 앱 최신 버전 조회 | | |

## APi 상세 설명

### 앱 최신 버전 조회

  * URI 
    * GET /v1/configs/versions
  * params
    * app
  * response
  
```json
  {
    "ver": 5,
    "ver_required": 5,
    "trycount": -1,
    "refurl": ""
  }
```

