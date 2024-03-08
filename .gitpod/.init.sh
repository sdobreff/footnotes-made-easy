#!/bin/bash

sudo service mysql stop
sudo mv /var/lib/mysql/ /workspace/
sudo cp .gitpod/mysqld.cnf /etc/mysql/mysql.conf.d/mysql.cnf
sudo service mysql start

export REPO_NAME=$(basename $GITPOD_REPO_ROOT)

sudo ln -s $GITPOD_REPO_ROOT /var/www/html/wp-content/plugins
sudo chown gitpod:gitpod /var/www/html/wp-content/plugins/$REPO_NAME

sudo ln -s $GITPOD_REPO_ROOT /var/www/html-multi/wp-content/plugins
sudo chown gitpod:gitpod /var/www/html-multi/wp-content/plugins/$REPO_NAME

sudo ln -s $GITPOD_REPO_ROOT/.gitpod-vscode /var/www/html/.vscode
sudo chown gitpod:gitpod /var/www/html/.vscode

sudo ln -s $GITPOD_REPO_ROOT/.gitpod-vscode /var/www/html-multi/.vscode
sudo chown gitpod:gitpod /var/www/html-multi/.vscode

sudo mv /var/www/html/wp-content/plugins/ /workspace/
sudo rm -rf /var/www/html/wp-content/plugins/
sudo mv /var/www/html-multi/wp-content/plugins/ /workspace/plugins-multi/
sudo rm -rf /var/www/html-multi/wp-content/plugins/
sudo mv /var/www/html/wp-content/uploads/ /workspace/
sudo rm -rf /var/www/html/wp-content/uploads/
sudo mv /var/www/html-multi/wp-content/uploads/ /workspace/uploads-multi/
sudo rm -rf /var/www/html-multi/wp-content/uploads/
sudo ln -s /workspace/plugins /var/www/html/wp-content
sudo ln -s /workspace/plugins-multi /var/www/html-multi/wp-content/plugins
sudo ln -s /workspace/uploads /var/www/html/wp-content
sudo ln -s /workspace/uploads-multi /var/www/html-multi/wp-content/uploads

# cp -a .gdrive $HOME/.gdrive

# FLAG="$GITPOD_REPO_ROOT/bin/install-dependencies.sh"

# search the flag file
# if [ -f $FLAG ]; then
#  /bin/bash $FLAG
# fi

# FLAG="$GITPOD_REPO_ROOT/bin/set-assets.sh"

# search the flag file
# if [ -f $FLAG ]; then
#  /bin/bash $FLAG
# fi

sudo adduser gitpod www-data
sudo chown gitpod:www-data /var/www -R
sudo chmod g+rw /var/www -R

sudo mysql -u root wordpress-multi -e "update wp_site set domain='81-$HOSTNAME.$GITPOD_WORKSPACE_CLUSTER_HOST' where id=1";
sudo mysql -u root wordpress-multi -e "update wp_blogs set domain='81-$HOSTNAME.$GITPOD_WORKSPACE_CLUSTER_HOST' where blog_id=1";
sudo mysql -u root wordpress-multi -e "update wp_options set option_value='https://81-$HOSTNAME.$GITPOD_WORKSPACE_CLUSTER_HOST/' where option_name='siteurl'";
sudo mysql -u root wordpress-multi -e "update wp_options set option_value='https://81-$HOSTNAME.$GITPOD_WORKSPACE_CLUSTER_HOST/' where option_name='home'";

cp .pre-commit .git/hooks/pre-commit

sudo crontab /usr/local/crons
