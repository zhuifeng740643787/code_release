<Modal
    width="1000"
    :mask-closable="false"
    :closable="true"
    v-model="progressModal.visible"
    title="发布进度"
>
    <Card v-for="(item, host) in progressModal.hosts">
        <div slot="title" class="step-card-wrapper">
            <span class="card-title">{{ host }}</span>
            <div class="card-loading-box">
                <Spin v-show="true">
                    <Icon type="load-c" size=10 class="spin-icon-load"></Icon>
                </Spin>
            </div>
        </div>
        <Steps :current="item.rate">
            <Step v-for="(content, index) in progressModal.steps"
                  :title="index<item.rate?'已完成':(index==item.rate?'进行中':'待进行')" :content="content"></Step>
        </Steps>
    </Card>
    <p slot="footer"></p>
</Modal>