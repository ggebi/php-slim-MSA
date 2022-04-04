# yesfile 모바일 다운로드 test db

mobile service의 test db입니다.


## 개발환경 구성

소스코드 다운로드
``` bash
$ git clone ssh://git@git.bankmedia.co.kr:10022/yesfile/backend-api-testdb.git
```

도커 네트워크 설정
```bash
$ docker network create yesfile
```

도커 컨테이너 실행
```bash
$ docker-compose up
```