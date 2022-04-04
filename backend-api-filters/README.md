# yesfile filter 서비스

yesfile 제휴 컨텐츠 필터링, 제휴판매 로그 전송을 위한 서비스 API입니다.

## 요구사항

* docker 최신버전
* docker-compose
* git

## 개발환경 구성

### 소스코드 다운로드
``` bash
$ git clone ssh://git@git.bankmedia.co.kr:10022/yesfile/backend-api-filters.git
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
| DB_ENV_MDB_PASSWORD | MDB 패스워드 |
| DB_ENV_SDB_PASSWORD | SDB 패스워드 |
| DB_ENV_COREDB_PASSWORD | COREDB 패스워드 |

3. 이미지 빌드
``` bash
$ docker build -t backend-api-filters .
```

4. 로컬 테스트용 개발환경 컨테이너 실행
``` bash
$ docker-compose up
```

## API 기능 설명
| HTTP METHOD | POST | GET | PUT | DELETE |
| ----------- | ---- | --- | --- | ------ |
| /v1/filters | | 제휴컨텐츠 필터링 | | |
| /v1/filters/paylog | 구매 기록 전송(캔들미디어) | | | |
| /v1/filters/imbclog | 구매 기록 전송(imbc) | | | |

## APi 상세 설명

제휴컨텐츠 필터링

  * URI
    * GET /v1/filters
  * params
    * seller
    * nickname
    * content_id
    * title
  * return
    * 200 - 구매리스트 정보

  * URI
    * POST /v1/filters/paylog
  * params
    * sell_userid
    * bbs_idx
    * content_title
    * now_time
    * mode
    * filter_files

  * URI
    * POST /v1/filters/imbclog
  * params
    * send_data_arr
    * sellerid
    * buyerid
    * payid
    * webck
    * bbs_title
    * bbs_num