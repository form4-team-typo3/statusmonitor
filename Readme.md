# form4_statusmonitor

## Publishing Package to GitLab Package Registry (Composer)

To release a new version of the package, follow these steps:

1. Create a tag with the new version name, for example, `1.0.1`.
2. Push the newly created tag to the repository.
3. The GitLab job `publish-composer-package` will automatically publish the new version to our package registry.

You can access our package registry [here](https://git.form4.de/groups/form4/typo3/typo3.extensions/-/packages).

