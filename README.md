## Setting up Slack

Go to: `https://<your slack name here>.slack.com/apps/manage/custom-integrations`

Replacing `<your slack name here>` with your actual slack name.

In the search bar, search `Bots` The top returned result should have the description: `Connect a bot to the Slack Real Time Messaging API.` Choose this one.

Click `Add Configuration` and type in the username for your bot.

Once saved, take note of the API Token displayed on the next page.

In your browser, now remove everything from the URL except for `https://<your slack name here>.slack.com`

Navigate to the channel you wish your bot to post in (or create a new channel). In the URL you will see: `https://<your slack name>.slack.com/messages/<channel id here>/`

Take note of the contents found at `<channel id here>` in the example above.

## Setting up WebTask.io

Create a new webtask.io account.

Once created, make a new Webtask function.

Above the part of the page that displays the code is a spanner icon. Click this and choose `Secrets` from the options provided.

In the new part of the window that loads to the left, click `Add secret` and provide the following details:

* Secret key: `slack`
* Secret value: `Your Slack API Token in "Setting up Slack"`

Save this and click `Add Secret` again. This time the values are:

* Secret key: `channel`
* Secret value: `Your channel Id in "Setting up Slack"`

Save this.

Now in your section of the page that displays the code. Paste the following code into this:

```js
var request = require('request');

/**
* @param context {WebtaskContext}
*/
module.exports = function(context, cb) {
  // cb(null, { hello: context.query.name || 'Anonymous' });
  return postToSlack(context, cb);
};

function postToSlack(context, cb) {
  request.post({
    uri: 'https://slack.com/api/chat.postMessage',
    form: {
      token: context.secrets.slack,
      channel: context.secrets.channel,
      text: context.data.authorName + ' has posted a new blog post titled: ' + context.data.blogTitle,
    },
    headers: {
      'content-type': 'application-x-www-form-urlencoded; charset=utf-8',
      'Authorization': 'Bearer ' + context.secrets.slack
    }
  },
  function (error, res, body) {
    cb(error, body)
  });
}
```

At the bottom of the page will be your Webtask URL, take note of this.

## Setting up the Symfony blog

```bash
git clone git@github.com:GregHolmes/symfony-blog-webtask.git
cd symfony-blog-webtask
```

Install the dependencies with the following command:

```bash
composer install
```

Create a docker database

```bash
docker run --name symfony-blog-mysql \
    -p 3306:3306 \
    -e MYSQL_ROOT_PASSWORD=myextremellysecretpassword \
    -e MYSQL_DATABASE=symfony-webtask \
    -e MYSQL_USER=symfony-webtask-user \
    -e MYSQL_PASSWORD=mysecretpassword \
    -d mysql:5.7
```

We're going to need API keys for Auth0, so <a href="https://auth0.com/signup" data-amp-replace="CLIENT_ID" data-amp-addparams="anonId=CLIENT_ID(cid-scope-cookie-fallback-name)">create your free account</a>. Once signed up:

- In the dashboard, click `Clients` on the left:
  * Create Client
  * Add a name
  * Choose _Regular Web Applications_ type
- Configure callback URL:
  * In the new Auth0 `Client`, go to the settings tab.
  * Find the text box labeled `Allowed Callback URLs`.
  * Paste the following in: `http://127.0.0.1:8000/auth0/callback`.
- Configure Auth0 Client to require usernames:
  * In the navigation bar find and click `Connections`
  * Then click `Database`
  * Click on `Username-Password-Authentication`
  * Toggle `Requires Username` to on.
- Go to the [Clients](https://manage.auth0.com/#/clients) section again and pick your `Client`.

en in your `.env` file paste the following, but replace the brackets and their contents with the details found in your client. Also update your database details shown below:

```yml
###> doctrine/doctrine-bundle ###
# Format described at http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#connecting-using-a-url
# For an SQLite database, use: "sqlite:///%kernel.project_dir%/var/data.db"
# Configure your db driver and server_version in config/packages/doctrine.yaml
DATABASE_URL=mysql://symfony-webtask-user:mysecretpassword@127.0.0.1:3306/symfony-webtask
###< doctrine/doctrine-bundle ###

DATABASE_NAME=symfony-webtask
AUTH0_CLIENT_ID={AUTH0_CLIENT_ID}
AUTH0_CLIENT_SECRET={AUTH0_CLIENT_SECRET}
AUTH0_DOMAIN={AUTH0_DOMAIN}
```

Create the database and update the schema:

```bash
php bin/console doctrine:database:create
php bin/console doctrine:schema:update --force
```

> __NOTE:__ If you do not have [Yarn](https://yarnpkg.com) installed (a JavaScript package manager), you will need to install and configure this. So, go to their [installation](https://yarnpkg.com/lang/en/docs/install/) page and follow the instructions for installing and configuring Yarn first.

Now you will need to install third-party libraries (used to make the blog look nicer visually) with the following commands:

```bash
yarn install
yarn run encore dev
```

Finally, run the development server with the following command:

```bash
php bin/console server:run
```

## See the blog

Open your browser to the following URL: `http://127.0.0.1/blog`
Click on the `Login` button and sign up your Author user with Auth0.
Once registered, click the `Admin` button in the top right hand corner of the screen.
You will be required to complete details about your author. Fill this data in and submit.

Click the `Admin` button again and then the `Add entry` button

Complete the fields to create a new Blog post. On submission of this. You should then see a new post in your Slack channel saying something along the lines of:

`<author name> has posted a new blog post titled: <blog post title>`

