# JSON content

JSON-content is the BoltCMS extension allow to create and edit content using REST API


## Configuration

After extension was installed you need change access token in the extension's config. 
This token need to authenticate requests of your HTTP client.

Config example:

```yaml
path: /api/content
auth:
  enabled: true
  access_token: LeeD7che3sohs8ou8iizegeepai9oup
```


## REST schema


### View 

View record by `id`

Example: 

```shell script
curl -X GET http://localhost:8000/api/content/entry/1
```


### Create

Create record from json

Example:

```shell script
curl -X POST \
     -H 'Content-Type: application/json' \
     -H 'X-Auth-Token: LeeD7che3sohs8ou8iizegeepai9oup' \ 
     -d '{"title":"My new entry","slug":"my-new-entry","ownerid":"1","status":"published"}' \
     http://localhost:8000/api/content/entry
```

You can create content entity with taxonomies.

POST request example with more advanced JSON:

```http request
POST http://localhost:8000/api/content/posts
Content-Type: application/json
X-Auth-Token: LeeD7che3sohs8ou8iizegeepai9oup

{
  "slug": "new-blog-post",
  "ownerid": 1,
  "status": "published",
  "title": "New blog post",
  "image": {
    "file": "2020-01/post-image.jpg"
  },
  "taxonomy": {
    "tags": [
      {
        "name": "News",
        "slug": "news"
      },
      {
        "name": "Review",
        "slug": "reviews"
      }
    ],
    "categories": [
      {
        "slug": "news",
        "name": "News"
      }
    ]
  }
}
```


### Patch

Patch content entity fields

Example: 

```shell script
curl -X PATCH \
     -H 'Content-Type: application/json' \
     -H 'X-Auth-Token: LeeD7che3sohs8ou8iizegeepai9oup' \ 
     -d '{"title":"Patched title"}' \
     http://localhost:8000/api/content/entry/1
```
