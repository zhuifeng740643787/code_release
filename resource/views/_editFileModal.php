<Modal
  v-model="fileContentModal.visible"
  :title="fileContentModal.title"
  @on-ok="handleChangeFileContent(fileContentModal.index)"
  :width="80">
  <i-input v-model="fileContentModal.content" :autosize="{minRows: 10}" size="large" type="textarea"></i-input>
</Modal>