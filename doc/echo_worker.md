## EchoWorker

```json
{
    "user":"nobody",
    "group":"nobody",
    "apps":{
        "echo":{
            "listen":"tcp://127.0.0.1:5101",
            "worker_processes":1,
            "worker_class":"EchoWorker"
        }
    }
}
```

```sh
$ ./bin/workermand


```sh
$ telnet 127.0.0.1 5101
