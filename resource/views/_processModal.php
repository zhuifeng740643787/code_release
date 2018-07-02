<Modal
  width="1000"
  :mask-closable="false"
  :closable="true"
  v-model="progressModal.visible"
  :title="progressModal.title">
  <Card>
    <div slot="title" class="step-card-wrapper">
      <span class="card-title">组任务</span>
      <div class="card-loading-box">
        <Spin v-show="progressModal.group.progress == 0">
          <Icon type="load-c" size=10 class="spin-icon-load"></Icon>
        </Spin>
      </div>
      <Alert type="error" class="error-tip-box" show-icon v-show="progressModal.group.progress==-1">{{ progressModal.group.error }}</Alert>
    </div>
    <Steps :current="progressModal.group.currentStep">
      <Step v-for="(step,index) in progressModal.group.steps"
            :title="progressModal.group.currentStep>index?'已完成':(progressModal.group.currentStep==index?'进行中':'待进行')" :content="step.name"></Step>
    </Steps>
  </Card>
  <Card>
    <div slot="title" class="step-card-wrapper">
      <span class="card-title">子任务</span>
      <div class="card-loading-box">
        <Spin v-show="progressModal.sub.progress == 0">
          <Icon type="load-c" size=10 class="spin-icon-load"></Icon>
        </Spin>
      </div>
    </div>
    <Card v-for="item in progressModal.sub.tasks">
      <div slot="title" class="step-card-wrapper">
        <span class="card-title">{{ item.server_name }}({{ item.server_host }})</span>
        <div class="card-loading-box">
          <Spin v-show="item.progress == 0">
            <Icon type="load-c" size=10 class="spin-icon-load"></Icon>
          </Spin>
        </div>
        <Alert type="error" class="error-tip-box" show-icon v-show="item.progress==-1">{{ item.error }}</Alert>
      </div>
      <Steps :current="item.currentStep">
        <Step v-for="(step,index) in progressModal.sub.steps"
              :title="item.currentStep>index?'已完成':(item.currentStep==index?'进行中':'待进行')" :content="step.name"></Step>
      </Steps>
    </Card>
  </Card>
  <p slot="footer"></p>
</Modal>