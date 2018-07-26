#!/bin/bash
# make sure lint-staged does not consider automatic fixing done by phpcbf an error (exit 0)
phpcbf $@
exit 0