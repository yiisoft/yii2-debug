#!/bin/sh
for file in main toolbar timeline; do sass scss/$file.scss css/$file.css --sourcemap=none; done
