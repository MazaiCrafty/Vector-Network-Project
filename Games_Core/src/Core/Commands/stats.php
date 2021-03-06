<?php
/**
 * Created by PhpStorm.
 * User: InkoHX
 * Date: 2018/07/17
 * Time: 12:50
 */

namespace Core\Commands;

use Core\DataFile;
use Core\Main;
use Core\Player\KD;
use Core\Player\Level;
use Core\Player\Money;
use Core\Player\Rank;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class stats extends PluginCommand
{
	protected $plugin;
	protected $level;
	protected $money;
	protected $rank;
	protected $kd;

	public function __construct(Main $plugin)
	{
		parent::__construct("stats", $plugin);
		$this->setPermission("vector.network.player");
		$this->setDescription("自分のステータスを表示します。");
		$this->plugin = $plugin;
		$this->level = new Level();
		$this->money = new Money();
		$this->rank = new Rank($this->plugin);
		$this->kd = new KD();
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (!$this->plugin->isEnabled()) {
			return false;
		}
		if (!$this->testPermission($sender)) {
			return false;
		}
		if ($sender instanceof Player) {
			$name = $sender->getName();
			$datafile = new DataFile($name);
			$userdata = $datafile->get('USERDATA');
			$ffapvp = $datafile->get('FFAPVP');
			$speedcorepvp = $datafile->get('COREPVP');
			$level = $this->level->getLevel($name);
			$money = $this->money->getMoney($name);
			$exp = $this->level->getExp($name);
			$firstlogin = $userdata['firstlogin'];
			$lastlogin = $userdata['lastlogin'];
			$maxexp = $userdata['maxexp'];
			$ffapvp_kill = $ffapvp['kill'];
			$ffapvp_death = $ffapvp['death'];
			$ffapvp_kd = $this->kd->FFAKD($name);
			$rank = $this->rank->getRank($name);
			$tag = $userdata['tag'];
			$speedcorepvp_kill = $speedcorepvp['kill'];
			$speedcorepvp_death = $speedcorepvp['death'];
			$speedcorepvp_breakcore = $speedcorepvp['breakcore'];
			$speedcorepvp_win = $speedcorepvp['win'];
			$speedcorepvp_lose = $speedcorepvp['lose'];
			$speedcorepvp_kd = $this->kd->SCPKD($sender->getName());
			$speedcorepvp_wl = $this->kd->SCPWL($sender->getName());
			$status = [
				"type" => "custom_form",
				"title" => "§l$name のステータス",
				"content" => [
					[
						"type" => "label",
						"text" => "現在の§bレベル§r: $level\n現在の§e経験値§r: $exp XP (次のレベルアップまで: $maxexp xp必要です。)\n§6V§bN§eCoin§r: $money\nRank: $rank\nタグ: $tag\n参加した日: $firstlogin\n最終ログイン日: $lastlogin"
					],
					[
						"type" => "label",
						"text" => "---=== §6FFA§cPvP §r===---\n§eキル数§r: $ffapvp_kill\n§cデス数§r: $ffapvp_death\n§eK§7/§cD§r: $ffapvp_kd"
					],
					[
						"type" => "label",
						"text" => "---=== §bSpeed§aCore§cPvP §r===---\n§eキル数§r: $speedcorepvp_kill\n§cデス数§r: $speedcorepvp_death\n§aコア§c破壊回数§r: $speedcorepvp_breakcore\n§aWin§r: $speedcorepvp_win\n §cLose§r: $speedcorepvp_lose\n§eK§7/§cD§r: $speedcorepvp_kd\n§aW§7/§cL§r: $speedcorepvp_wl"
					]
				]
			];
			$modal = new ModalFormRequestPacket();
			$modal->formId = mt_rand(1111111, 9999999);
			$modal->formData = json_encode($status);
			$sender->dataPacket($modal);
			return true;
		}
		$sender->sendMessage(TextFormat::RED . "このコマンドはプレイヤーのみが実行できます。");
		return true;
	}
}
