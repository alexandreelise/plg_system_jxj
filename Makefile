.PHONY: gen doc all

.DEFAULT_GOAL= all
CURRENT_DIR=$$(pwd)
CURRENT_DATETIME=$$(date +%Y%m%d)
BUILD_DIR=$$(pwd)

gen: ./src ## Create extension zip file
	mkdir -p $(BUILD_DIR)/build \
	&& cd $(CURRENT_DIR)/src \
	&& find . -type f -name "*.php" -exec php -l "{}" \; \
	&& zip -9 -r $$(dirname $(BUILD_DIR))/build/$$(basename $$(dirname $(CURRENT_DIR)))_$(CURRENT_DATETIME).zip . \
	&& cd ..

doc: ./src ./docs ./tests ## Generate documentation
	$(CURRENT_DIR)/vendor/bin/phpdoc

all: gen doc
