# -*- mode: ruby -*-
# vi: set ft=ruby :

VAGRANTFILE_API_VERSION = "2"
Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
  config.vm.provider :virtualbox do |vb|
    vb.customize ["modifyvm", :id, "--memory", "1024"]
    vb.customize ["modifyvm", :id, "--natdnshostresolver1", "on"]
  end

  config.vm.box = "ubuntu/trusty64"
  config.vm.network "private_network", ip: "10.0.0.222"
  config.vm.provision "shell", path: "provisioner.sh"

  config.vm.synced_folder "./", "/app/", owner: "vagrant", group: "www-data", mount_options: ["dmode=775,fmode=664"]
end
