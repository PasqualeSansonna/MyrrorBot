const Telegraf = require('telegraf');
const bot = new Telegraf("630735263:AAG4-t_0S8s4YauTlo78UWkl5oK3hzAq5zQ");
bot.start((message) => {
  console.log('started:', message.from.id)
  return message.reply('Hello my friend, write anything to start search!!');
})
bot.startPolling();